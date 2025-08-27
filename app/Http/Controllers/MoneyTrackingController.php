<?php

namespace App\Http\Controllers;

use App\Models\MoneyAccount;
use App\Models\MoneyTransfer;
use App\Models\Business;
use App\Models\Client;
use App\Models\ContractorProfile;
use App\Services\MoneyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoneyTrackingController extends Controller
{
    protected $moneyTrackingService;

    public function __construct()
    {
        $this->moneyTrackingService = new MoneyTrackingService();
    }

    /**
     * Show money tracking dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $business = $user->business;
        $isSuperBusiness = $business->id === 1; // Kashtre super business

        // Get account balances
        $accountBalances = $this->getAccountBalances($business);

        // Get recent transfers - for super business, show all transfers
        $transfersQuery = MoneyTransfer::with(['fromAccount', 'toAccount', 'client', 'invoice', 'business']);
        if (!$isSuperBusiness) {
            $transfersQuery->where('business_id', $business->id);
        }
        $recentTransfers = $transfersQuery->orderBy('created_at', 'desc')->limit(20)->get();

        // Get client accounts with balances
        $clientAccountsQuery = MoneyAccount::where('type', 'client_account')->with('client', 'business');
        if (!$isSuperBusiness) {
            $clientAccountsQuery->where('business_id', $business->id);
        }
        $clientAccounts = $clientAccountsQuery->orderBy('balance', 'desc')->limit(10)->get();

        // Get contractor accounts with balances
        $contractorAccountsQuery = MoneyAccount::where('type', 'contractor_account')->with('contractorProfile.user', 'business');
        if (!$isSuperBusiness) {
            $contractorAccountsQuery->where('business_id', $business->id);
        }
        $contractorAccounts = $contractorAccountsQuery->orderBy('balance', 'desc')->limit(10)->get();

        // For super business, get business summary
        $businessSummary = null;
        if ($isSuperBusiness) {
            $businessSummary = $this->getBusinessSummary();
        }

        return view('money-tracking.dashboard', compact(
            'accountBalances',
            'recentTransfers',
            'clientAccounts',
            'contractorAccounts',
            'isSuperBusiness',
            'businessSummary'
        ));
    }

    /**
     * Get account balances for a business
     */
    public function getAccountBalances(Business $business)
    {
        $accounts = MoneyAccount::where('business_id', $business->id)
            ->whereNull('client_id')
            ->whereNull('contractor_profile_id')
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->type] = [
                'name' => $account->name,
                'balance' => $account->balance,
                'formatted_balance' => $account->formatted_balance,
                'currency' => $account->currency
            ];
        }

        return $balances;
    }

    /**
     * Get client account details
     */
    public function getClientAccount(Request $request, Client $client)
    {
        $account = $this->moneyTrackingService->getOrCreateClientAccount($client);
        
        $transfers = MoneyTransfer::where('client_id', $client->id)
            ->with(['fromAccount', 'toAccount', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'balance' => $account->balance,
                'formatted_balance' => $account->formatted_balance,
                'currency' => $account->currency
            ],
            'transfers' => $transfers
        ]);
    }

    /**
     * Get contractor account details
     */
    public function getContractorAccount(Request $request, ContractorProfile $contractor)
    {
        $account = $this->moneyTrackingService->getOrCreateContractorAccount($contractor);
        
        $transfers = MoneyTransfer::where('to_account_id', $account->id)
            ->orWhere('from_account_id', $account->id)
            ->with(['fromAccount', 'toAccount', 'invoice', 'item'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'balance' => $account->balance,
                'formatted_balance' => $account->formatted_balance,
                'currency' => $account->currency
            ],
            'transfers' => $transfers
        ]);
    }

    /**
     * Get transfer history
     */
    public function getTransferHistory(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;

        $query = MoneyTransfer::where('business_id', $business->id)
            ->with(['fromAccount', 'toAccount', 'client', 'invoice', 'item']);

        // Apply filters
        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'transfers' => $transfers
        ]);
    }

    /**
     * Get account summary
     */
    public function getAccountSummary(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;

        // Get all account types and their balances
        $accounts = MoneyAccount::where('business_id', $business->id)
            ->whereNull('client_id')
            ->whereNull('contractor_profile_id')
            ->get();

        $summary = [];
        foreach ($accounts as $account) {
            $summary[] = [
                'type' => $account->type,
                'name' => $account->name,
                'balance' => $account->balance,
                'formatted_balance' => $account->formatted_balance,
                'currency' => $account->currency
            ];
        }

        // Get total client accounts balance
        $totalClientBalance = MoneyAccount::where('business_id', $business->id)
            ->where('type', 'client_account')
            ->sum('balance');

        // Get total contractor accounts balance
        $totalContractorBalance = MoneyAccount::where('business_id', $business->id)
            ->where('type', 'contractor_account')
            ->sum('balance');

        $summary[] = [
            'type' => 'total_client_accounts',
            'name' => 'Total Client Accounts',
            'balance' => $totalClientBalance,
            'formatted_balance' => number_format($totalClientBalance, 2) . ' UGX',
            'currency' => 'UGX'
        ];

        $summary[] = [
            'type' => 'total_contractor_accounts',
            'name' => 'Total Contractor Accounts',
            'balance' => $totalContractorBalance,
            'formatted_balance' => number_format($totalContractorBalance, 2) . ' UGX',
            'currency' => 'UGX'
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Process refund manually
     */
    public function processRefund(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'amount' => 'required|numeric|min:0.01',
                'reason' => 'required|string|max:255',
                'approved_by' => 'required|exists:users,id',
            ]);

            $client = Client::findOrFail($validated['client_id']);
            $user = Auth::user();

            // Check if user has permission to process refunds
            if (!$this->canProcessRefund($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to process refunds.'
                ], 403);
            }

            // Process refund through money tracking system
            $transfer = $this->moneyTrackingService->processRefund(
                $client,
                $validated['amount'],
                $validated['reason'],
                $validated['approved_by']
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'transfer' => $transfer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get business summary for super business
     */
    private function getBusinessSummary()
    {
        $businesses = Business::where('id', '>', 1)->get(); // Exclude Kashtre super business
        
        $summary = [];
        foreach ($businesses as $business) {
            // Get total balances for this business
            $totalClientBalance = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'client_account')
                ->sum('balance');
                
            $totalContractorBalance = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'contractor_account')
                ->sum('balance');
                
            $totalSuspenseBalance = MoneyAccount::where('business_id', $business->id)
                ->whereIn('type', ['package_suspense_account', 'general_suspense_account', 'kashtre_suspense_account'])
                ->sum('balance');
                
            $totalBusinessBalance = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'business_account')
                ->sum('balance');
            
            $summary[] = [
                'business' => $business,
                'total_client_balance' => $totalClientBalance,
                'total_contractor_balance' => $totalContractorBalance,
                'total_suspense_balance' => $totalSuspenseBalance,
                'total_business_balance' => $totalBusinessBalance,
                'total_transfers' => MoneyTransfer::where('business_id', $business->id)->count(),
                'recent_transfers' => MoneyTransfer::where('business_id', $business->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            ];
        }
        
        return $summary;
    }

    /**
     * Check if user can process refunds
     */
    private function canProcessRefund($user)
    {
        // Super admin can process refunds
        if ($user->business_id === 1) {
            return true;
        }

        // Check if user has refund permission
        $permissions = $user->permissions ?? [];
        return in_array('Process Refunds', $permissions) || in_array('Manage Finance', $permissions);
    }
}
