<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyVisitsController extends Controller
{
    /**
     * Display daily visits record
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;
        
        // Check if current branch exists
        if (!$currentBranch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned. Please contact administrator.');
        }
        
        // Get the requested branch or use current branch
        $selectedBranchId = $request->get('branch_id', $currentBranch->id);
        
        // Check if user has access to the selected branch
        $allowedBranches = (array) ($user->allowed_branches ?? []);
        if (!in_array($selectedBranchId, $allowedBranches)) {
            $selectedBranchId = $currentBranch->id;
        }
        
        $selectedBranch = \App\Models\Branch::find($selectedBranchId) ?? $currentBranch;
        
        // Date filter (default to today if not specified)
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        
        // For Kashtre (business_id == 1), show all invoices from all businesses
        if ($business->id == 1) {
            $dailyVisits = Invoice::where('business_id', '!=', 1)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->with(['client', 'business', 'branch', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Get summary stats for the selected date
            $totalVisits = Invoice::where('business_id', '!=', 1)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->count();
                
            $uniqueClients = Invoice::where('business_id', '!=', 1)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->distinct('client_id')
                ->count('client_id');
                
            $totalRevenue = Invoice::where('business_id', '!=', 1)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
                
            $totalPaid = Invoice::where('business_id', '!=', 1)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->sum('amount_paid');
        } else {
            // Get visits for the selected business and branch
            $dailyVisits = Invoice::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->with(['client', 'branch', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Get summary stats for the selected date
            $totalVisits = Invoice::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->count();
                
            $uniqueClients = Invoice::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->distinct('client_id')
                ->count('client_id');
                
            $totalRevenue = Invoice::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
                
            $totalPaid = Invoice::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->sum('amount_paid');
        }
        
        // Get all branches the user has access to for the filter
        $availableBranches = \App\Models\Branch::whereIn('id', $allowedBranches)->get();
        
        // Get date range for calendar picker (last 30 days and next 7 days)
        $minDate = now()->subDays(30)->format('Y-m-d');
        $maxDate = now()->addDays(7)->format('Y-m-d');
        
        return view('daily-visits.index', compact(
            'dailyVisits',
            'totalVisits',
            'uniqueClients',
            'totalRevenue',
            'totalPaid',
            'business',
            'currentBranch',
            'selectedBranch',
            'availableBranches',
            'selectedDate',
            'minDate',
            'maxDate'
        ));
    }
}
