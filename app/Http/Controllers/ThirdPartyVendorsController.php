<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ThirdPartyApiService;
use Illuminate\Support\Facades\Log;

class ThirdPartyVendorsController extends Controller
{
    protected $apiService;

    public function __construct(ThirdPartyApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display a listing of connected third party vendors
     */
    public function index()
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        try {
            // Get connected vendors from third-party API
            $baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get("{$baseUrl}/api/v1/businesses/{$business->id}/connected-vendors");

            $vendors = [];
            
            if ($response->successful()) {
                $data = $response->json();
                $vendors = $data['data'] ?? [];
                
                // Load local ThirdPartyPayer records and merge status information
                $payers = \App\Models\ThirdPartyPayer::where('business_id', $business->id)
                    ->where('type', 'insurance_company')
                    ->whereNull('client_id')
                    ->get()
                    ->keyBy('insurance_company_id');
                
                // Merge payer status into vendor data
                foreach ($vendors as &$vendor) {
                    if (isset($payers[$vendor['id']])) {
                        $payer = $payers[$vendor['id']];
                        $vendor['payer_status'] = $payer->status;
                        $vendor['payer_id'] = $payer->id;
                    }
                }
                unset($vendor);
            } else {
                Log::warning('Failed to fetch connected vendors', [
                    'business_id' => $business->id,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return view('third-party-vendors.index', compact('vendors', 'business'));
        } catch (\Exception $e) {
            Log::error('Exception while fetching connected vendors', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return view('third-party-vendors.index', [
                'vendors' => [],
                'business' => $business,
                'error' => 'Failed to load connected vendors. Please try again later.',
            ]);
        }
    }

    /**
     * Display detailed balance history for a specific third party vendor
     */
    public function show($vendorId)
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        try {
            // Get vendor details from third-party API
            $baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get("{$baseUrl}/api/v1/businesses/{$business->id}/connected-vendors");

            $vendor = null;
            $vendors = [];
            
            if ($response->successful()) {
                $data = $response->json();
                $vendors = $data['data'] ?? [];
                
                // Find the specific vendor
                foreach ($vendors as $v) {
                    if ($v['id'] == $vendorId) {
                        $vendor = $v;
                        break;
                    }
                }
            }

            if (!$vendor) {
                return redirect()->route('third-party-vendors.index')
                    ->with('error', 'Third party vendor not found.');
            }

            // Find the insurance company in Kashtre database by code (optional - for display)
            $insuranceCompany = \App\Models\InsuranceCompany::where('code', $vendor['code'])
                ->first();

            // Find the third-party payer for this vendor
            // The vendor ID from the third-party API is the insurance_company_id stored in third_party_payers
            // This is because clients store the third-party system's insurance company ID, not Kashtre's
            $thirdPartyPayer = \App\Models\ThirdPartyPayer::where('insurance_company_id', $vendorId)
                ->where('business_id', $business->id)
                ->where('type', 'insurance_company')
                ->whereNull('client_id') // Business-level
                ->first();

            $balanceHistories = collect();
            $totalCredits = 0;
            $totalDebits = 0;
            $currentBalance = 0;

            $invoices = collect();
            $excludedItemsForPayer = collect();
            $items = collect();
            
            if ($thirdPartyPayer) {
                // Get recent balance history (last 10 for summary on details page)
                $balanceHistories = \App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                    ->with(['invoice', 'client', 'business', 'branch', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                // Calculate totals
                $totalCredits = \App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                    ->where('transaction_type', 'credit')
                    ->sum('change_amount');
                
                $totalDebits = abs(\App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                    ->where('transaction_type', 'debit')
                    ->sum('change_amount'));

                $currentBalance = $thirdPartyPayer->current_balance ?? 0;

                // Resolve excluded items for this third-party payer (service exclusions on Kashtre side)
                $excludedItemIds = (array) ($thirdPartyPayer->excluded_items ?? []);
                if (!empty($excludedItemIds)) {
                    $excludedItemsForPayer = \App\Models\Item::where('business_id', $business->id)
                        ->whereIn('id', $excludedItemIds)
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'type']);
                }

                // Load all items for this business so exclusions can be managed from this page
                $items = \App\Models\Item::where('business_id', $business->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code', 'type']);

                // Get invoices from balance history (debit entries with invoices)
                $invoiceEntries = \App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                    ->where('transaction_type', 'debit')
                    ->whereNotNull('invoice_id')
                    ->with(['invoice', 'invoice.client', 'client', 'business', 'branch'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Group by invoice and calculate totals
                $processedInvoiceIds = [];
                foreach ($invoiceEntries as $entry) {
                    if (!$entry->invoice || in_array($entry->invoice_id, $processedInvoiceIds)) {
                        continue;
                    }

                    $processedInvoiceIds[] = $entry->invoice_id;

                    // Get all entries for this invoice
                    $allInvoiceEntries = \App\Models\ThirdPartyPayerBalanceHistory::where('invoice_id', $entry->invoice_id)
                        ->where('third_party_payer_id', $thirdPartyPayer->id)
                        ->get();

                    // Calculate totals
                    $debits = abs($allInvoiceEntries->where('transaction_type', 'debit')->sum('change_amount'));
                    $credits = $allInvoiceEntries->where('transaction_type', 'credit')->sum('change_amount');
                    
                    $totalAmount = $debits;
                    $amountPaid = $credits;
                    $balanceDue = max(0, $totalAmount - $amountPaid);
                    
                    // Determine payment status
                    if ($balanceDue <= 0) {
                        $paymentStatus = 'paid';
                    } elseif ($amountPaid > 0) {
                        $paymentStatus = 'partial';
                    } else {
                        $paymentStatus = $entry->payment_status ?? 'pending_payment';
                    }

                    $invoices->push([
                        'id' => $entry->invoice_id,
                        'invoice_number' => $entry->invoice->invoice_number ?? 'N/A',
                        'client_name' => $entry->client ? $entry->client->name : ($entry->invoice->client_name ?? 'N/A'),
                        'client_id' => $entry->client ? $entry->client->client_id : null,
                        'total_amount' => $totalAmount,
                        'amount_paid' => $amountPaid,
                        'balance_due' => $balanceDue,
                        'payment_status' => $paymentStatus,
                        'status' => $entry->invoice->status ?? 'confirmed',
                        'created_at' => $entry->invoice->created_at ? $entry->invoice->created_at : $entry->created_at,
                        'business_name' => $entry->business ? $entry->business->name : null,
                        'branch_name' => $entry->branch ? $entry->branch->name : null,
                    ]);
                }
            }

            // Also fetch synced transactions from the vendor system
            $syncedTransactions = collect();
            try {
                $vendorResponse = \Illuminate\Support\Facades\Http::timeout(10)
                    ->get("{$baseUrl}/api/v1/transactions/by-vendor/{$vendorId}");
                
                if ($vendorResponse->successful()) {
                    $syncedData = $vendorResponse->json();
                    $syncedTransactions = collect($syncedData['data'] ?? []);
                    
                    Log::info('Fetched synced transactions from vendor system', [
                        'vendor_id' => $vendorId,
                        'count' => $syncedTransactions->count(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch synced transactions from vendor system', [
                    'vendor_id' => $vendorId,
                    'error' => $e->getMessage(),
                ]);
            }

            return view('third-party-vendors.show', compact(
                'vendor',
                'business',
                'insuranceCompany',
                'thirdPartyPayer',
                'balanceHistories',
                'totalCredits',
                'totalDebits',
                'currentBalance',
                'invoices',
                'excludedItemsForPayer',
                'items',
                'syncedTransactions'
            ));
        } catch (\Exception $e) {
            Log::error('Exception while fetching vendor details', [
                'vendor_id' => $vendorId,
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('third-party-vendors.index')
                ->with('error', 'Failed to load vendor details. Please try again later.');
        }
    }

    /**
     * Display dedicated balance statement page for a third party vendor
     */
    public function balanceStatement($vendorId)
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        try {
            // Get vendor details from third-party API
            $baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get("{$baseUrl}/api/v1/businesses/{$business->id}/connected-vendors");

            $vendor = null;
            
            if ($response->successful()) {
                $data = $response->json();
                $vendors = $data['data'] ?? [];
                
                // Find the specific vendor
                foreach ($vendors as $v) {
                    if ($v['id'] == $vendorId) {
                        $vendor = $v;
                        break;
                    }
                }
            }

            if (!$vendor) {
                return redirect()->route('third-party-vendors.index')
                    ->with('error', 'Third party vendor not found.');
            }

            // Find the third-party payer for this vendor
            $thirdPartyPayer = \App\Models\ThirdPartyPayer::where('insurance_company_id', $vendorId)
                ->where('business_id', $business->id)
                ->where('type', 'insurance_company')
                ->whereNull('client_id') // Business-level
                ->first();

            if (!$thirdPartyPayer) {
                return redirect()->route('third-party-vendors.show', $vendorId)
                    ->with('error', 'No third-party payer account found for this vendor. Balance history will appear here once invoices are created with this vendor.');
            }

            // Get all balance history records with all relationships
            $balanceHistories = \App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                ->orderBy('created_at', 'desc')
                ->with(['invoice', 'client', 'business', 'branch', 'user'])
                ->paginate(50);

            // Calculate totals
            $totalCredits = \App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                ->where('transaction_type', 'credit')
                ->sum('change_amount');
            
            $totalDebits = abs(\App\Models\ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                ->where('transaction_type', 'debit')
                ->sum('change_amount'));

            $currentBalance = $thirdPartyPayer->current_balance ?? 0;

            return view('third-party-vendors.balance-statement', compact(
                'vendor',
                'business',
                'thirdPartyPayer',
                'balanceHistories',
                'totalCredits',
                'totalDebits',
                'currentBalance'
            ));
        } catch (\Exception $e) {
            Log::error('Exception while fetching vendor balance statement', [
                'vendor_id' => $vendorId,
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('third-party-vendors.index')
                ->with('error', 'Failed to load balance statement. Please try again later.');
        }
    }

    /**
     * Block a vendor.
     */
    public function block(Request $request, $vendorId)
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        // Find the ThirdPartyPayer record
        $payer = \App\Models\ThirdPartyPayer::where('insurance_company_id', $vendorId)
            ->where('business_id', $business->id)
            ->where('type', 'insurance_company')
            ->whereNull('client_id')
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'status' => 'required|in:blocked,suspended',
        ]);

        $statusLabel = $validated['status'] === 'suspended' ? 'Suspended' : 'Blocked';
        
        $payer->block(
            $validated['reason'],
            auth()->id(),
            $validated['status']
        );

        return redirect()
            ->route('third-party-vendors.show', $vendorId)
            ->with('success', "Vendor {$statusLabel} successfully.");
    }

    /**
     * Reactivate a blocked/suspended vendor.
     */
    public function reactivate(Request $request, $vendorId)
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        // Find the ThirdPartyPayer record
        $payer = \App\Models\ThirdPartyPayer::where('insurance_company_id', $vendorId)
            ->where('business_id', $business->id)
            ->where('type', 'insurance_company')
            ->whereNull('client_id')
            ->firstOrFail();

        if (!$payer->isBlocked() && !$payer->isSuspended()) {
            return redirect()
                ->route('third-party-vendors.show', $vendorId)
                ->with('error', 'Vendor is not blocked or suspended.');
        }

        $payer->reactivate();

        return redirect()
            ->route('third-party-vendors.show', $vendorId)
            ->with('success', 'Vendor reactivated successfully.');
    }

    /**
     * Create a ThirdPartyPayer account for a vendor that doesn't have one
     */
    public function createPayer($vendorId)
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        try {
            // Get vendor details from third-party API
            $baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get("{$baseUrl}/api/v1/businesses/{$business->id}/connected-vendors");

            $vendor = null;
            
            if ($response->successful()) {
                $data = $response->json();
                $vendors = $data['data'] ?? [];
                
                // Find the specific vendor
                foreach ($vendors as $v) {
                    if ($v['id'] == $vendorId) {
                        $vendor = $v;
                        break;
                    }
                }
            }

            if (!$vendor) {
                return redirect()->route('third-party-vendors.index')
                    ->with('error', 'Third party vendor not found.');
            }

            // Check if payer account already exists
            $existingPayer = \App\Models\ThirdPartyPayer::where('insurance_company_id', $vendorId)
                ->where('business_id', $business->id)
                ->where('type', 'insurance_company')
                ->whereNull('client_id')
                ->first();

            if ($existingPayer) {
                return redirect()->route('third-party-vendors.show', $vendorId)
                    ->with('info', 'Payer account already exists for this vendor.');
            }

            // Find or create InsuranceCompany record
            $insuranceCompany = \App\Models\InsuranceCompany::where('code', $vendor['code'])
                ->first();

            if (!$insuranceCompany) {
                // Create insurance company record if it doesn't exist
                $insuranceCompany = \App\Models\InsuranceCompany::create([
                    'business_id' => $business->id,
                    'name' => $vendor['name'],
                    'code' => $vendor['code'],
                    'email' => $vendor['email'] ?? null,
                    'phone' => $vendor['phone'] ?? null,
                    'third_party_business_id' => $vendorId,
                ]);

                Log::info('Created insurance company for vendor', [
                    'insurance_company_id' => $insuranceCompany->id,
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor['name'],
                ]);
            }

            // Create ThirdPartyPayer account
            $payer = \App\Models\ThirdPartyPayer::create([
                'business_id' => $business->id,
                'type' => 'insurance_company',
                'insurance_company_id' => $insuranceCompany->id,
                'name' => $vendor['name'],
                'email' => $vendor['email'] ?? null,
                'phone_number' => $vendor['phone'] ?? null,
                'status' => 'active',
                'credit_limit' => $business->max_third_party_credit_limit ?? 10000.00,
            ]);

            Log::info('Created third-party payer account', [
                'third_party_payer_id' => $payer->id,
                'vendor_id' => $vendorId,
                'vendor_name' => $vendor['name'],
                'business_id' => $business->id,
            ]);

            return redirect()->route('third-party-vendors.show', $vendorId)
                ->with('success', 'Payer account created successfully for ' . $vendor['name'] . '. Financial data will now be available.');

        } catch (\Exception $e) {
            Log::error('Exception while creating payer account', [
                'vendor_id' => $vendorId,
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('third-party-vendors.show', $vendorId)
                ->with('error', 'Failed to create payer account. Error: ' . $e->getMessage());
        }
    }
}
