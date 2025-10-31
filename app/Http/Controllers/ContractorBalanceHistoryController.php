<?php

namespace App\Http\Controllers;

use App\Models\ContractorBalanceHistory;
use App\Models\ContractorProfile;
use App\Models\ContractorWithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorBalanceHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // For super business (Kashtre), show all contractors
        if ($user->business_id == 1) {
            $contractors = ContractorProfile::all();
            $contractorBalanceHistories = ContractorBalanceHistory::with('contractorProfile')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // For regular businesses, show only their contractors
            $contractors = ContractorProfile::where('business_id', $user->business_id)->get();
            $contractorBalanceHistories = ContractorBalanceHistory::with('contractorProfile')
                ->whereHas('contractorProfile', function($query) use ($user) {
                    $query->where('business_id', $user->business_id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('contractor-balance-statement.index', compact('contractorBalanceHistories', 'contractors'));
    }

    public function show(ContractorProfile $contractorProfile)
    {
        $user = Auth::user();

        // Ensure the user has access to this contractor's history
        if ($user->business_id != 1 && $user->business_id != $contractorProfile->business_id) {
            abort(403, 'Unauthorized access.');
        }

        $contractorBalanceHistories = ContractorBalanceHistory::with('contractorProfile')
            ->where('contractor_profile_id', $contractorProfile->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Compute totals from full history (not just current page)
        $totalCredits = ContractorBalanceHistory::where('contractor_profile_id', $contractorProfile->id)
            ->whereIn('type', ['credit', 'package'])
            ->sum('amount');

        $totalDebits = ContractorBalanceHistory::where('contractor_profile_id', $contractorProfile->id)
            ->where('type', 'debit')
            ->sum('amount');

        $availableBalance = $totalCredits - $totalDebits;

        // Get recent withdrawal requests for this contractor
        $recentWithdrawalRequests = ContractorWithdrawalRequest::where('contractor_profile_id', $contractorProfile->id)
            ->with('requestedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending withdrawal request count
        $pendingWithdrawalCount = ContractorWithdrawalRequest::where('contractor_profile_id', $contractorProfile->id)
            ->whereIn('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'processing'])
            ->count();

        return view('contractor-balance-statement.show', compact(
            'contractorProfile',
            'contractorBalanceHistories',
            'totalCredits',
            'totalDebits',
            'availableBalance',
            'recentWithdrawalRequests',
            'pendingWithdrawalCount'
        ));
    }
}

