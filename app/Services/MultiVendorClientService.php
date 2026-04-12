<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientVendor;
use App\Models\ThirdPartyPayer;
use Illuminate\Support\Facades\Log;

class MultiVendorClientService
{
    private $apiService;

    public function __construct()
    {
        $this->apiService = new ThirdPartyApiService();
    }

    /**
     * Attach multiple vendors to a client with vendor-specific settings
     * Verifies each policy and extracts payment responsibility from API response
     */
    public function attachMultipleVendors(Client $client, array $vendorData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($vendorData as $vendorId => $data) {
            try {
                // Check if vendor is suspended or blocked
                $vendor = ThirdPartyPayer::findOrFail($vendorId);
                
                if ($vendor->isSuspended() || $vendor->isBlocked()) {
                    $results['failed'][$vendorId] = "Vendor is {$vendor->status}. Cannot register client with this vendor.";
                    continue;
                }

                // Verify policy with API and extract payment responsibility
                $policyNumber = $data['policy_number'] ?? null;
                $paymentResponsibility = null;
                $policyVerified = false;

                if ($policyNumber) {
                    try {
                        // Build full name from client
                        $fullName = trim($client->surname . ' ' . $client->first_name . ' ' . ($client->other_names ?? ''));
                        
                        // Verify policy with third-party API
                        $verificationResult = $this->apiService->verifyPolicyNumber(
                            $vendor->business_id,
                            $policyNumber,
                            !empty($fullName) ? $fullName : null,
                            $client->date_of_birth,
                            $client->services_category
                        );

                        if ($verificationResult && isset($verificationResult['success']) && $verificationResult['success']) {
                            $policyVerified = true;
                            
                            // Extract payment responsibility from API response
                            if (isset($verificationResult['data']['payment_responsibility'])) {
                                $paymentResponsibility = $verificationResult['data']['payment_responsibility'];
                            }
                            
                            Log::info('Policy verified for vendor', [
                                'client_id' => $client->id,
                                'vendor_id' => $vendorId,
                                'policy_number' => $policyNumber,
                                'verification_method' => $verificationResult['verification_method'] ?? 'unknown',
                            ]);
                        } else {
                            Log::warning('Policy verification failed for vendor', [
                                'client_id' => $client->id,
                                'vendor_id' => $vendorId,
                                'policy_number' => $policyNumber,
                                'error' => $verificationResult['message'] ?? 'Unknown error',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Exception during policy verification', [
                            'client_id' => $client->id,
                            'vendor_id' => $vendorId,
                            'policy_number' => $policyNumber,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Create or update ClientVendor record with payment responsibility from API
                $clientVendorData = [
                    'policy_number' => $policyNumber,
                    'policy_verified' => $policyVerified,
                    'physical_insurance_card_verified' => $data['physical_insurance_card_verified'] ?? false,
                    'is_open_enrollment' => $data['is_open_enrollment'] ?? false,
                    'status' => 'active',
                ];

                // Populate payment responsibility fields from API response
                if ($paymentResponsibility) {
                    $clientVendorData['deductible_amount'] = $paymentResponsibility['deductible_amount'] ?? null;
                    $clientVendorData['copay_amount'] = $paymentResponsibility['copay_amount'] ?? null;
                    $clientVendorData['coinsurance_percentage'] = $paymentResponsibility['coinsurance_percentage'] ?? null;
                    $clientVendorData['copay_max_limit'] = $paymentResponsibility['copay_max_limit'] ?? null;
                    $clientVendorData['copay_contributes_to_deductible'] = $paymentResponsibility['copay_contributes_to_deductible'] ?? false;
                    $clientVendorData['coinsurance_contributes_to_deductible'] = $paymentResponsibility['coinsurance_contributes_to_deductible'] ?? false;
                }

                $clientVendor = ClientVendor::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'third_party_payer_id' => $vendorId,
                    ],
                    $clientVendorData
                );

                $results['success'][$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor->name,
                    'policy_number' => $policyNumber,
                    'policy_verified' => $policyVerified,
                    'has_payment_responsibility' => $paymentResponsibility !== null,
                ];

                Log::info('Client attached to vendor with payment responsibility', [
                    'client_id' => $client->id,
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor->name,
                    'policy_verified' => $policyVerified,
                    'payment_responsibility' => $paymentResponsibility ? 'extracted from API' : 'not available',
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to attach vendor to client', [
                    'client_id' => $client->id,
                    'vendor_id' => $vendorId,
                    'error' => $e->getMessage(),
                ]);

                $results['failed'][$vendorId] = "Error: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Register authorized visits for a client with multiple vendors
     */
    public function registerAuthorizedVisitsMultiVendor(Client $client): array
    {
        $results = [
            'registered' => [],
            'failed' => [],
        ];

        $vendors = $client->vendors()->where('status', 'active')->get();

        foreach ($vendors as $clientVendor) {
            try {
                if (!$clientVendor->policy_number) {
                    $results['failed'][$clientVendor->third_party_payer_id] = 'No policy number set for this vendor';
                    continue;
                }

                // Resolve the third-party's business_id — the API expects this, not the Kashtre-local third_party_payer_id
                $thirdPartyPayer = ThirdPartyPayer::find($clientVendor->third_party_payer_id);
                if (!$thirdPartyPayer || !$thirdPartyPayer->business_id) {
                    $results['failed'][$clientVendor->third_party_payer_id] = 'Vendor has no business_id configured';
                    continue;
                }

                $visitRegistrationResult = $this->apiService->registerAuthorizedVisit(
                    $client,
                    $client->visit_id,
                    now()->toDateString(),
                    $client->visit_expires_at ? $client->visit_expires_at->toDateTimeString() : null,
                    $client->services_category,
                    $thirdPartyPayer->business_id
                );

                if ($visitRegistrationResult) {
                    $results['registered'][$clientVendor->third_party_payer_id] = $visitRegistrationResult;
                    
                    Log::info('Authorized visit registered for vendor', [
                        'client_id' => $client->id,
                        'vendor_id' => $clientVendor->third_party_payer_id,
                        'visit_id' => $client->visit_id,
                    ]);
                } else {
                    $results['failed'][$clientVendor->third_party_payer_id] = 'Visit registration failed';
                }

            } catch (\Exception $e) {
                Log::warning('Failed to register authorized visit for vendor', [
                    'client_id' => $client->id,
                    'vendor_id' => $clientVendor->third_party_payer_id,
                    'error' => $e->getMessage(),
                ]);

                $results['failed'][$clientVendor->third_party_payer_id] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Handle item clearing/charges distribution across vendors
     * This allows configuring which items are cleared by which vendor
     */
    public function configureItemClearingStrategy(Client $client, array $itemClearingConfig): array
    {
        $results = [
            'configured' => [],
            'failed' => [],
        ];

        foreach ($itemClearingConfig as $vendorId => $itemsToClean) {
            try {
                $clientVendor = ClientVendor::where('client_id', $client->id)
                    ->where('third_party_payer_id', $vendorId)
                    ->firstOrFail();

                // Store excluded items (items NOT cleared by this vendor)
                $allItems = $client->excluded_items ?? [];
                $itemsToExclude = array_diff($allItems, $itemsToClean);

                $clientVendor->update([
                    'excluded_items' => $itemsToExclude,
                ]);

                $results['configured'][$vendorId] = [
                    'clears_items' => $itemsToClean,
                    'excludes_items' => $itemsToExclude,
                ];

                Log::info('Item clearing strategy configured for vendor', [
                    'client_id' => $client->id,
                    'vendor_id' => $vendorId,
                    'cleared_count' => count($itemsToClean),
                    'excluded_count' => count($itemsToExclude),
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to configure item clearing for vendor', [
                    'client_id' => $client->id,
                    'vendor_id' => $vendorId,
                    'error' => $e->getMessage(),
                ]);

                $results['failed'][$vendorId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get all active vendors for a client
     */
    public function getActiveVendors(Client $client)
    {
        return $client->vendors()
            ->where('status', 'active')
            ->with('vendor')
            ->get();
    }

    /**
     * Suspend a client's relationship with a specific vendor
     */
    public function suspendVendor(Client $client, int $vendorId, string $reason = null): bool
    {
        try {
            ClientVendor::where('client_id', $client->id)
                ->where('third_party_payer_id', $vendorId)
                ->update([
                    'status' => 'suspended',
                    'notes' => $reason,
                ]);

            Log::info('Client vendor relationship suspended', [
                'client_id' => $client->id,
                'vendor_id' => $vendorId,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to suspend vendor for client', [
                'client_id' => $client->id,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reactivate a client's relationship with a vendor
     */
    public function reactivateVendor(Client $client, int $vendorId): bool
    {
        try {
            ClientVendor::where('client_id', $client->id)
                ->where('third_party_payer_id', $vendorId)
                ->update([
                    'status' => 'active',
                    'notes' => null,
                ]);

            Log::info('Client vendor relationship reactivated', [
                'client_id' => $client->id,
                'vendor_id' => $vendorId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reactivate vendor for client', [
                'client_id' => $client->id,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
