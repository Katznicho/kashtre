<?php

namespace App\Http\Controllers;

use App\Models\ContractorWithdrawalRequest;
use App\Models\ContractorProfile;
use App\Models\WithdrawalSetting;
use App\Models\BusinessWithdrawalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractorWithdrawalRequestController extends Controller
{
    /**
     * Display a listing of contractor withdrawal requests
     */
    public function index(ContractorProfile $contractorProfile)
    {
        $user = Auth::user();
        
        // Check if user has access to this contractor
        if ($user->business_id != 1 && $user->business_id != $contractorProfile->business_id) {
            abort(403, 'Unauthorized access.');
        }

        $withdrawalRequests = ContractorWithdrawalRequest::where('contractor_profile_id', $contractorProfile->id)
            ->with('requestedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('contractor-withdrawal-requests.index', compact('contractorProfile', 'withdrawalRequests'));
    }

    /**
     * Show the form for creating a new contractor withdrawal request
     */
    public function create(ContractorProfile $contractorProfile)
    {
        $user = Auth::user();
        
        // Check if user has access to this contractor
        if ($user->business_id != 1 && $user->business_id != $contractorProfile->business_id) {
            abort(403, 'Unauthorized access.');
        }

        // Check if contractor already has a pending withdrawal request
        $pendingRequest = ContractorWithdrawalRequest::where('contractor_profile_id', $contractorProfile->id)
            ->whereIn('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'processing'])
            ->first();

        if ($pendingRequest) {
            return redirect()->route('contractor-balance-statement.show', $contractorProfile)
                ->with('info', 'This contractor already has a pending withdrawal request. Please wait for it to be processed before creating a new one.');
        }

        // Get withdrawal settings for the business (optional; allow page even if none configured)
        $withdrawalSettings = WithdrawalSetting::where('business_id', $contractorProfile->business_id)
            ->where('is_active', true)
            ->get();

        // Get business withdrawal charges (optional; default to zero if none)
        $withdrawalCharges = BusinessWithdrawalSetting::where('business_id', $contractorProfile->business_id)
            ->where('is_active', true)
            ->orderBy('lower_bound')
            ->get();

        $business = $contractorProfile->business;
        
        // Compute current contractor available balance from full history
        $totalCredits = \App\Models\ContractorBalanceHistory::where('contractor_profile_id', $contractorProfile->id)
            ->whereIn('type', ['credit', 'package'])
            ->sum('amount');
        $totalDebits = \App\Models\ContractorBalanceHistory::where('contractor_profile_id', $contractorProfile->id)
            ->where('type', 'debit')
            ->sum('amount');
        $currentBalance = $totalCredits - $totalDebits;

        return view('contractor-withdrawal-requests.create', compact('contractorProfile', 'withdrawalSettings', 'withdrawalCharges', 'business', 'currentBalance'));
    }

    /**
     * Store a newly created contractor withdrawal request
     */
    public function store(Request $request, ContractorProfile $contractorProfile)
    {
        $user = Auth::user();
        
        // Check if user has access to this contractor
        if ($user->business_id != 1 && $user->business_id != $contractorProfile->business_id) {
            abort(403, 'Unauthorized access.');
        }

        // Check if contractor already has a pending withdrawal request
        $pendingRequest = ContractorWithdrawalRequest::where('contractor_profile_id', $contractorProfile->id)
            ->whereIn('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'processing'])
            ->first();

        if ($pendingRequest) {
            return redirect()->route('contractor-balance-statement.show', $contractorProfile)
                ->with('info', 'This contractor already has a pending withdrawal request.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_type' => 'required|in:regular,express',
            'reason' => 'nullable|string|max:1000',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'mobile_money_number' => 'nullable|string|max:255',
            // payment_method removed from UI; optional backend field if provided
            'payment_method' => 'nullable|in:bank_transfer,mobile_money',
        ]);

        DB::beginTransaction();
        
        try {
            // Get withdrawal settings for the selected type (optional)
            $withdrawalSetting = WithdrawalSetting::where('business_id', $contractorProfile->business_id)
                ->where('withdrawal_type', $request->withdrawal_type)
                ->where('is_active', true)
                ->first();

            // Calculate withdrawal charge
            $amount = $request->amount;
            $withdrawalCharge = $this->calculateWithdrawalCharge($amount, $contractorProfile->business_id);
            // For contractor withdrawals, payout to contractor is the full requested amount
            // The system debits amount + charge behind the scenes during processing
            $netAmount = $amount;

            // Check if contractor has sufficient balance (amount + charge)
            if ($contractorProfile->account_balance < ($amount + $withdrawalCharge)) {
                throw new \Exception('Insufficient contractor account balance for withdrawal.');
            }

            // Determine payment method (UI removed) - default to bank_transfer
            $paymentMethod = $request->payment_method ?: 'bank_transfer';

            // Create contractor withdrawal request
            $withdrawalRequest = ContractorWithdrawalRequest::create([
                'contractor_profile_id' => $contractorProfile->id,
                'business_id' => $contractorProfile->business_id,
                'requested_by' => $user->id,
                'amount' => $amount,
                'withdrawal_charge' => $withdrawalCharge,
                'net_amount' => $netAmount,
                'withdrawal_type' => $request->withdrawal_type,
                'status' => 'pending',
                'reason' => $request->reason,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'mobile_money_number' => $request->mobile_money_number,
                'payment_method' => $paymentMethod,
                'required_business_approvals' => $withdrawalSetting->min_business_initiators ?? 0,
                'required_kashtre_approvals' => $withdrawalSetting->min_kashtre_initiators ?? 1,
            ]);

            // Contractor withdrawals bypass the business steps but should enter Kashtre approval (not pre-approved)
            $withdrawalRequest->update([
                'status' => 'business_approved', // business stage marked complete
                'business_approvals_count' => $withdrawalSetting->min_business_initiators ?? 0,
                'current_business_step' => 4,
                'business_approved_at' => now(),
                'current_kashtre_step' => 1,
            ]);

            DB::commit();

            $successMessage = 'Contractor withdrawal request created successfully. The request has been sent directly to Kashtre approval.';

            return redirect()->route('contractor-balance-statement.show', $contractorProfile)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified contractor withdrawal request
     */
    public function show(ContractorWithdrawalRequest $contractorWithdrawalRequest)
    {
        $user = Auth::user();
        
        // Check if user has access to this withdrawal request
        if ($user->business_id != 1 && $user->business_id != $contractorWithdrawalRequest->business_id) {
            abort(403, 'Unauthorized access.');
        }

        return view('contractor-withdrawal-requests.show', compact('contractorWithdrawalRequest'));
    }

    /**
     * Kashtre approver approves current step
     */
    public function approve(ContractorWithdrawalRequest $contractorWithdrawalRequest)
    {
        $user = Auth::user();
        
        // Only Kashtre (business_id = 1) can approve at Kashtre level
        if ($user->business_id != 1) {
            abort(403, 'Only Kashtre approvers can approve.');
        }

        // Must be in Kashtre approval phase
        if (!in_array($contractorWithdrawalRequest->status, ['business_approved', 'kashtre_approved'])) {
            return back()->with('error', 'Request is not in Kashtre approval phase.');
        }

        // Check permission to approve at current step
        if (!$contractorWithdrawalRequest->canUserApproveAtCurrentStep($user)) {
            return back()->with('error', 'You cannot approve at this step.');
        }

        $currentStep = $contractorWithdrawalRequest->getCurrentStepNumber();
        
        // Set status to kashtre_approved when entering Kashtre approval (first approval)
        if ($contractorWithdrawalRequest->status === 'business_approved' && $currentStep == 1) {
            $contractorWithdrawalRequest->status = 'kashtre_approved';
            $contractorWithdrawalRequest->save();
        }
        
        $contractorWithdrawalRequest->updateStepApprovalCounts('kashtre', $currentStep);
        
        // Refresh the model to get updated approval counts
        $contractorWithdrawalRequest->refresh();
        
        // Check if current step has enough approvals (at least 1 required per step)
        $stepField = "kashtre_step_{$currentStep}_approvals";
        $approvalCount = $contractorWithdrawalRequest->$stepField;
        
        // Only move to next step if current step has at least 1 approval
        // This ensures Step 1 completes before Step 2, Step 2 before Step 3
        if ($approvalCount >= 1) {
            // Move to next step or complete
            $contractorWithdrawalRequest->moveToNextStep();
            $contractorWithdrawalRequest->refresh();
            
            if ($currentStep == 3 && $contractorWithdrawalRequest->status === 'approved') {
                return back()->with('success', 'Request fully approved and processing.');
            } elseif ($currentStep < 3) {
                $nextStepLevel = $contractorWithdrawalRequest->getStepApprovalLevel($currentStep + 1);
                return back()->with('success', 'Step ' . $currentStep . ' (' . ucfirst($currentStep == 1 ? 'Initiator' : ($currentStep == 2 ? 'Authorizer' : 'Approver')) . ') approved. Request moved to Step ' . ($currentStep + 1) . ' (' . ucfirst($nextStepLevel) . ').');
            }
        }

        return back()->with('success', 'Approval recorded for Step ' . $currentStep . '.');
    }

    /**
     * Kashtre approver rejects
     */
    public function reject(Request $request, ContractorWithdrawalRequest $contractorWithdrawalRequest)
    {
        $user = Auth::user();
        
        if ($user->business_id != 1) {
            abort(403, 'Only Kashtre approvers can reject.');
        }

        $reason = $request->input('reason');

        $contractorWithdrawalRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Request rejected.');
    }

    /**
     * Calculate withdrawal charge based on amount and business settings
     */
    private function calculateWithdrawalCharge($amount, $businessId)
    {
        $charge = BusinessWithdrawalSetting::where('business_id', $businessId)
            ->where('is_active', true)
            ->where('lower_bound', '<=', $amount)
            ->where('upper_bound', '>=', $amount)
            ->first();

        if (!$charge) {
            return 0;
        }

        if ($charge->charge_type === 'percentage') {
            return ($amount * $charge->charge_amount) / 100;
        } else {
            return $charge->charge_amount;
        }
    }
}
