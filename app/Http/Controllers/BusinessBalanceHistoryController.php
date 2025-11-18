<?php

namespace App\Http\Controllers;

use App\Models\BusinessBalanceHistory;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessBalanceHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get business account IDs for filtering (only show business_account type, not suspense accounts)
        $businessAccountIds = \App\Models\MoneyAccount::where('type', 'business_account')
            ->when($user->business_id != 1, function($query) use ($user) {
                return $query->where('business_id', $user->business_id);
            })
            ->pluck('id');
        
        // For super business (Kashtre), show all businesses
        if ($user->business_id == 1) {
            $businesses = Business::all();
            $businessBalanceHistories = BusinessBalanceHistory::with('business')
                ->whereIn('money_account_id', $businessAccountIds)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // For regular businesses, show only their own history
            $businesses = Business::where('id', $user->business_id)->get();
            $businessBalanceHistories = BusinessBalanceHistory::with('business')
                ->where('business_id', $user->business_id)
                ->whereIn('money_account_id', $businessAccountIds)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // Calculate totals from filtered records
        $totalCredits = BusinessBalanceHistory::whereIn('money_account_id', $businessAccountIds)
            ->when($user->business_id != 1, function($query) use ($user) {
                return $query->where('business_id', $user->business_id);
            })
            ->where('type', 'credit')
            ->sum('amount');
        
        $totalDebits = BusinessBalanceHistory::whereIn('money_account_id', $businessAccountIds)
            ->when($user->business_id != 1, function($query) use ($user) {
                return $query->where('business_id', $user->business_id);
            })
            ->where('type', 'debit')
            ->sum('amount');

        return view('business-balance-statement.index', compact('businessBalanceHistories', 'businesses', 'totalCredits', 'totalDebits'))
            ->with('canUserCreateWithdrawal', function($user) {
                return $this->canUserCreateWithdrawal($user);
            });
    }

    public function show(Business $business)
    {
        $user = Auth::user();
        
        // Check if user has access to this business
        if ($user->business_id != 1 && $user->business_id != $business->id) {
            abort(403, 'Unauthorized access to business balance statement.');
        }

        // Get business account IDs for filtering (only show business_account type, not suspense accounts)
        $businessAccountIds = \App\Models\MoneyAccount::where('business_id', $business->id)
            ->where('type', 'business_account')
            ->pluck('id');

        $businessBalanceHistories = BusinessBalanceHistory::where('business_id', $business->id)
            ->whereIn('money_account_id', $businessAccountIds)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('business-balance-statement.show', compact('businessBalanceHistories', 'business'));
    }

    /**
     * Show Kashtre (super business) balance statement
     */
    public function kashtreStatement()
    {
        $user = Auth::user();
        
        // Only super business users can access Kashtre statement
        if ($user->business_id != 1) {
            abort(403, 'Unauthorized access to Kashtre balance statement.');
        }

        $kashtreBalanceHistories = BusinessBalanceHistory::where('business_id', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('kashtre-balance-statement.index', compact('kashtreBalanceHistories'));
    }

    /**
     * Show detailed Kashtre balance statement
     */
    public function kashtreStatementShow()
    {
        $user = Auth::user();
        
        // Only super business users can access Kashtre statement
        if ($user->business_id != 1) {
            abort(403, 'Unauthorized access to Kashtre balance statement.');
        }

        $kashtreBalanceHistories = BusinessBalanceHistory::where('business_id', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('kashtre-balance-statement.show', compact('kashtreBalanceHistories'));
    }

    /**
     * Check if a user can create withdrawal requests
     */
    private function canUserCreateWithdrawal($user)
    {
        // Check if user has withdrawal settings configured for their business
        $withdrawalSetting = \App\Models\WithdrawalSetting::where('business_id', $user->business_id)
            ->where('is_active', true)
            ->first();

        if (!$withdrawalSetting) {
            return false;
        }

        // Check if user is an initiator for this business
        $isInitiator = \App\Models\WithdrawalSettingApprover::where('withdrawal_setting_id', $withdrawalSetting->id)
            ->where('approver_id', $user->id)
            ->where('approver_type', 'user')
            ->where('approver_level', 'business')
            ->where('approval_level', 'initiator')
            ->exists();

        return $isInitiator;
    }
}

