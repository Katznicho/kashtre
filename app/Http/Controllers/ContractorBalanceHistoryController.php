<?php

namespace App\Http\Controllers;

use App\Models\ContractorBalanceHistory;
use App\Models\ContractorProfile;
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

        return view('contractor-balance-statement.show', compact('contractorProfile', 'contractorBalanceHistories'));
    }
}

