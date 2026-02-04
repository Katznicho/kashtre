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
                ->where('status', 'active')
                ->first();

            $balanceHistories = collect();
            $totalCredits = 0;
            $totalDebits = 0;
            $currentBalance = 0;

            $invoices = collect();
            
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

            return view('third-party-vendors.show', compact(
                'vendor',
                'business',
                'insuranceCompany',
                'thirdPartyPayer',
                'balanceHistories',
                'totalCredits',
                'totalDebits',
                'currentBalance',
                'invoices'
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
                ->where('status', 'active')
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
}
