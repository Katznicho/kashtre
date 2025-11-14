<?php

namespace App\Http\Controllers;

use App\Models\BankSchedule;
use App\Models\Business;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Only allow Kashtre users (business_id = 1) with proper permissions
            if (auth()->user()->business_id !== 1) {
                abort(403, 'Access denied. Bank schedules are only accessible to Kashtre administrators.');
            }
            
            // Check for Manage Bank Schedules permission
            if (!in_array('Manage Bank Schedules', auth()->user()->permissions ?? [])) {
                abort(403, 'Access denied. You do not have permission to manage bank schedules.');
            }
            
            return $next($request);
        });
    }

    public function index()
    {
        return view('bank-schedules.index-livewire');
    }

    public function create()
    {
        // Only Kashtre users can access (already checked in constructor)
        $businesses = Business::where('id', '!=', 1)->get();

        $withdrawalRequests = WithdrawalRequest::where('status', 'approved')
            ->where('withdrawal_type', 'bank_transfer')
            ->where('business_id', '!=', 1) // Only show requests from other businesses
            ->with(['business'])
            ->latest()
            ->get();

        return view('bank-schedules.create', compact('businesses', 'withdrawalRequests'));
    }

    public function store(Request $request)
    {
        // Only Kashtre users can access (already checked in constructor)
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|not_in:1', // Cannot create for Kashtre business
            'client_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_charge' => 'nullable|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
            'withdrawal_request_id' => 'nullable|exists:withdrawal_requests,id',
            'reference_id' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,processed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $bankSchedule = BankSchedule::create([
                'business_id' => $validated['business_id'],
                'client_name' => $validated['client_name'],
                'amount' => $validated['amount'],
                'withdrawal_charge' => $validated['withdrawal_charge'] ?? 0,
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'withdrawal_request_id' => $validated['withdrawal_request_id'] ?? null,
                'reference_id' => $validated['reference_id'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('bank-schedules.index')
                ->with('success', 'Bank schedule created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create bank schedule: ' . $e->getMessage());
        }
    }

    public function show(BankSchedule $bankSchedule)
    {
        // Only Kashtre users can access (already checked in constructor)
        $bankSchedule->load(['business', 'withdrawalRequest', 'creator']);

        return view('bank-schedules.show', compact('bankSchedule'));
    }

    public function edit(BankSchedule $bankSchedule)
    {
        // Only Kashtre users can access (already checked in constructor)
        $businesses = Business::where('id', '!=', 1)->get();

        $withdrawalRequests = WithdrawalRequest::where('status', 'approved')
            ->where('withdrawal_type', 'bank_transfer')
            ->where('business_id', '!=', 1) // Only show requests from other businesses
            ->with(['business'])
            ->latest()
            ->get();

        return view('bank-schedules.edit', compact('bankSchedule', 'businesses', 'withdrawalRequests'));
    }

    public function update(Request $request, BankSchedule $bankSchedule)
    {
        // Only Kashtre users can access (already checked in constructor)
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|not_in:1', // Cannot update to Kashtre business
            'client_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_charge' => 'nullable|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
            'withdrawal_request_id' => 'nullable|exists:withdrawal_requests,id',
            'reference_id' => 'nullable|string|max:255',
            'status' => 'required|in:pending,processed,cancelled',
        ]);

        try {
            // Ensure withdrawal_charge defaults to 0 if not provided
            $validated['withdrawal_charge'] = $validated['withdrawal_charge'] ?? 0;
            $bankSchedule->update($validated);

            return redirect()->route('bank-schedules.index')
                ->with('success', 'Bank schedule updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update bank schedule: ' . $e->getMessage());
        }
    }

    public function destroy(BankSchedule $bankSchedule)
    {
        // Only Kashtre users can access (already checked in constructor)

        try {
            $bankSchedule->delete();

            return redirect()->route('bank-schedules.index')
                ->with('success', 'Bank schedule deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete bank schedule: ' . $e->getMessage());
        }
    }
}
