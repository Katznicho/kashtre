<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VendorTransactionSyncService
{
    /**
     * Send a completed transaction to the vendor system for recording
     */
    public function syncTransactionToVendor(Transaction $transaction)
    {
        try {
            // Get invoice details
            $invoice = $transaction->invoice;
            if (!$invoice) {
                Log::warning('VendorTransactionSyncService: Invoice not found for transaction', [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $transaction->invoice_id,
                ]);
                return false;
            }

            // Get insurance authorizations from invoice
            $insuranceAuthorizations = $invoice->insurance_authorization_snapshot ?? [];
            
            if (empty($insuranceAuthorizations)) {
                Log::warning('VendorTransactionSyncService: No insurance authorizations found', [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
                return false;
            }

            // Send transaction to vendor for each insurance company
            $vendorUrl = config('services.vendor.api_url', 'http://localhost:8001');
            $vendorUrl = rtrim($vendorUrl, '/');
            
            $allSynced = true;
            
            // Check if vendors is an array (multi-vendor) or if this is old format
            $vendors = $insuranceAuthorizations['vendors'] ?? [$insuranceAuthorizations];
            
            foreach ($vendors as $auth) {
                // Support both old (insurance_company_id) and new (vendor_id) field names
                $vendorId = $auth['vendor_id'] ?? $auth['insurance_company_id'] ?? null;
                $vendorName = $auth['vendor_name'] ?? 'Unknown';
                
                if (!$vendorId) {
                    Log::warning('VendorTransactionSyncService: No vendor_id or insurance_company_id found', [
                        'transaction_id' => $transaction->id,
                        'auth_keys' => array_keys($auth)
                    ]);
                    continue;
                }

                // Get insurance portion (support both field names)
                $insurancePortion = (float) ($auth['insurance_total'] ?? $auth['insurance_portion'] ?? $auth['amount'] ?? 0);

                // Prepare transaction data for vendor
                $payload = [
                    'transaction_id' => $transaction->id,
                    'external_reference' => $transaction->external_reference,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id' => $transaction->client_id,
                    'client_name' => $transaction->names ?? $transaction->client->name ?? 'Unknown',
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'payment_status' => $transaction->payment_status,
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendorName,
                    'authorization_reference' => $auth['authorization_reference'] ?? null,
                    'insurance_portion' => $insurancePortion,
                    'transaction_date' => $transaction->date ?? now(),
                    'description' => $transaction->description,
                    'business_id' => $transaction->business_id,
                ];

                try {
                    $response = Http::timeout(10)
                        ->post("{$vendorUrl}/api/v1/transactions/record-from-kashtre", $payload);

                    if ($response->successful()) {
                        Log::info('VendorTransactionSyncService: Transaction synced successfully', [
                            'transaction_id' => $transaction->id,
                            'vendor_id' => $vendorId,
                            'vendor_name' => $vendorName,
                            'insurance_portion' => $insurancePortion,
                            'vendor_response' => $response->json(),
                        ]);
                    } else {
                        Log::warning('VendorTransactionSyncService: Vendor rejected transaction', [
                            'transaction_id' => $transaction->id,
                            'vendor_id' => $vendorId,
                            'vendor_name' => $vendorName,
                            'vendor_status' => $response->status(),
                            'vendor_body' => $response->body(),
                        ]);
                        $allSynced = false;
                    }
                } catch (\Exception $e) {
                    Log::error('VendorTransactionSyncService: Failed to sync transaction to vendor', [
                        'transaction_id' => $transaction->id,
                        'vendor_id' => $vendorId,
                        'vendor_name' => $vendorName,
                        'error' => $e->getMessage(),
                    ]);
                    $allSynced = false;
                }
            }

            return $allSynced;
        } catch (\Exception $e) {
            Log::error('VendorTransactionSyncService: Exception during sync', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
