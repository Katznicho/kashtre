<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\WithdrawalRequestApproval;
use App\Models\WithdrawalSetting;
use App\Models\BusinessWithdrawalSetting;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // For super business (Kashtre), show all withdrawal requests
        if ($user->business_id == 1) {
            $withdrawalRequests = WithdrawalRequest::with(['business', 'requester'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // For regular businesses, show only their own requests
            $withdrawalRequests = WithdrawalRequest::with(['business', 'requester'])
                ->where('business_id', $user->business_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('withdrawal-requests.index', compact('withdrawalRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user can create withdrawal requests
        if (!$this->canUserCreateWithdrawal($user)) {
            return redirect()->route('business-balance-statement.index')
                ->with('error', 'You do not have permission to create withdrawal requests. Please ensure withdrawal settings are configured and you are assigned as an initiator.');
        }

        // Check if user already has a pending withdrawal request
        $pendingRequest = WithdrawalRequest::where('business_id', $user->business_id)
            ->where('requested_by', $user->id)
            ->whereIn('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'processing'])
            ->first();

        if ($pendingRequest) {
            return redirect()->route('withdrawal-requests.show', $pendingRequest)
                ->with('info', 'You already have a pending withdrawal request. Please wait for it to be processed before creating a new one.');
        }

        // Get withdrawal settings for the business
        $withdrawalSettings = WithdrawalSetting::where('business_id', $user->business_id)
            ->where('is_active', true)
            ->get();

        if ($withdrawalSettings->isEmpty()) {
            return redirect()->route('business-balance-statement.index')
                ->with('error', 'No active withdrawal settings found for your business. Please contact your administrator.');
        }

        // Get business withdrawal charges
        $withdrawalCharges = BusinessWithdrawalSetting::where('business_id', $user->business_id)
            ->where('is_active', true)
            ->orderBy('lower_bound')
            ->get();

        if ($withdrawalCharges->isEmpty()) {
            return redirect()->route('business-balance-statement.index')
                ->with('error', 'No withdrawal charges configured for your business. Please contact your administrator.');
        }

        $business = $user->business;
        
        // Get current account balance
        $currentBalance = $business->account_balance ?? 0;

        return view('withdrawal-requests.create', compact('withdrawalSettings', 'withdrawalCharges', 'business', 'currentBalance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user can create withdrawal requests
        if (!$this->canUserCreateWithdrawal($user)) {
            return redirect()->route('business-balance-statement.index')
                ->with('error', 'You do not have permission to create withdrawal requests.');
        }

        // Check if user already has a pending withdrawal request
        $pendingRequest = WithdrawalRequest::where('business_id', $user->business_id)
            ->where('requested_by', $user->id)
            ->whereIn('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'processing'])
            ->first();

        if ($pendingRequest) {
            return redirect()->route('withdrawal-requests.show', $pendingRequest)
                ->with('info', 'You already have a pending withdrawal request. Please wait for it to be processed before creating a new one.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_type' => 'required|in:regular,express',
        ]);

        DB::beginTransaction();
        
        try {
            // Get withdrawal settings for the selected type
            $withdrawalSetting = WithdrawalSetting::where('business_id', $user->business_id)
                ->where('withdrawal_type', $request->withdrawal_type)
                ->where('is_active', true)
                ->first();

            if (!$withdrawalSetting) {
                throw new \Exception('Withdrawal settings not found for the selected type.');
            }

            // Check minimum withdrawal amount
            if ($request->amount < $withdrawalSetting->minimum_withdrawal_amount) {
                throw new \Exception('Amount must be at least ' . number_format($withdrawalSetting->minimum_withdrawal_amount, 2) . ' UGX.');
            }

            // Calculate withdrawal charge
            $withdrawalCharge = $this->calculateWithdrawalCharge($request->amount, $user->business_id);
            Log::info('WR Create - charge calculated', [
                'business_id' => $user->business_id,
                'amount' => (float) $request->amount,
                'computed_charge' => (float) $withdrawalCharge,
                'user_id' => $user->id,
            ]);
            
            // Total to be deducted from balance is amount + charge (handled at processing)
            $totalDeduction = $request->amount + $withdrawalCharge;

            // Create a single withdrawal request entry (no separate charge row)
            $withdrawalRequest = WithdrawalRequest::create([
                'business_id' => $user->business_id,
                'requested_by' => $user->id,
                'amount' => $request->amount,
                'withdrawal_charge' => $withdrawalCharge,
                'net_amount' => $request->amount, // payout equals requested amount
                'withdrawal_type' => $request->withdrawal_type,
                'status' => 'pending',
                'reason' => ucfirst($request->withdrawal_type) . ' Withdrawal',
                'required_business_approvals' => 3,
                'required_kashtre_approvals' => 3,
                // request_type is not needed since we only create one entry now
                'current_business_step' => 1, // Start at step 1
            ]);

            Log::info('WR Created', [
                'id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'business_id' => $withdrawalRequest->business_id,
                'amount' => (float) $withdrawalRequest->amount,
                'saved_withdrawal_charge' => (float) $withdrawalRequest->withdrawal_charge,
                'net_amount' => (float) $withdrawalRequest->net_amount,
                'status' => $withdrawalRequest->status,
            ]);

            // Auto-approve step 1 if creator is an initiator
            // Check if user is assigned as initiator at business level
            $withdrawalSetting = WithdrawalSetting::where('business_id', $user->business_id)
                ->where('withdrawal_type', $request->withdrawal_type)
                ->where('is_active', true)
                ->first();

            if ($withdrawalSetting) {
                $isInitiator = $withdrawalSetting->allBusinessApprovers()
                    ->where('approver_id', $user->id)
                    ->where('approval_level', 'initiator')
                    ->exists();

                if ($isInitiator && $withdrawalRequest->getCurrentStepNumber() == 1) {
                    // Create approval record for step 1
                    WithdrawalRequestApproval::create([
                        'withdrawal_request_id' => $withdrawalRequest->id,
                        'approver_id' => $user->id,
                        'approver_type' => 'user',
                        'approver_level' => 'business',
                        'approval_step' => 1,
                        'action' => 'approved',
                        'comment' => 'Auto-approved by initiator upon creation',
                    ]);

                    // Update step-specific approval counts
                    $this->updateStepApprovalCounts($withdrawalRequest, $user);

                    // Refresh to get updated counts
                    $withdrawalRequest->refresh();

                    // Check if step 1 is complete and move to step 2
                    if ($withdrawalRequest->hasApprovedCurrentStep()) {
                        $withdrawalRequest->moveToNextStep();
                    }
                }
            }

            DB::commit();

            return redirect()->route('withdrawal-requests.show', $withdrawalRequest)
                ->with('success', 'Request created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        $user = Auth::user();
        
        // Check if user has access to this withdrawal request
        if ($user->business_id != 1 && $withdrawalRequest->business_id != $user->business_id) {
            abort(403, 'Unauthorized access to withdrawal request.');
        }

        // Load relationships
        $withdrawalRequest->load(['business', 'requester', 'approvals.approver', 'processor']);

        return view('withdrawal-requests.show', compact('withdrawalRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WithdrawalRequest $withdrawalRequest)
    {
        // Withdrawal requests cannot be edited once created
        return redirect()->route('withdrawal-requests.show', $withdrawalRequest)
            ->with('info', 'Withdrawal requests cannot be edited once created.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        // Withdrawal requests cannot be updated once created
        return redirect()->route('withdrawal-requests.show', $withdrawalRequest)
            ->with('info', 'Withdrawal requests cannot be updated once created.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WithdrawalRequest $withdrawalRequest)
    {
        // Withdrawal requests cannot be deleted once created
        return redirect()->route('withdrawal-requests.index')
            ->with('info', 'Withdrawal requests cannot be deleted once created.');
    }

    /**
     * Check if a user can create withdrawal requests
     */
    private function canUserCreateWithdrawal($user)
    {
        // Check if user has withdrawal settings configured for their business
        $withdrawalSetting = WithdrawalSetting::where('business_id', $user->business_id)
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

    /**
     * Calculate withdrawal charge based on amount and business charges
     */
    private function calculateWithdrawalCharge($amount, $businessId)
    {
        // Find the matching bracket: lower_bound <= amount <= upper_bound
        $charge = BusinessWithdrawalSetting::where('business_id', $businessId)
            ->where('is_active', true)
            ->where('lower_bound', '<=', $amount)
            ->where('upper_bound', '>=', $amount)
            ->orderByDesc('lower_bound')
            ->first();

        if (!$charge) {
            Log::warning('WR Charge - no matching bracket', [
                'business_id' => $businessId,
                'amount' => (float) $amount,
            ]);
            return 0;
        }

        if ($charge->charge_type === 'fixed') {
            $computed = (float) $charge->charge_amount;
            Log::info('WR Charge - matched bracket (fixed)', [
                'business_id' => $businessId,
                'amount' => (float) $amount,
                'lower_bound' => (float) $charge->lower_bound,
                'upper_bound' => (float) $charge->upper_bound,
                'charge_amount' => (float) $charge->charge_amount,
                'computed' => $computed,
            ]);
            return $computed;
        }

        // Percentage
        $computed = (float) (($amount * $charge->charge_amount) / 100);
        Log::info('WR Charge - matched bracket (percentage)', [
            'business_id' => $businessId,
            'amount' => (float) $amount,
            'lower_bound' => (float) $charge->lower_bound,
            'upper_bound' => (float) $charge->upper_bound,
            'charge_amount' => (float) $charge->charge_amount,
            'computed' => $computed,
        ]);
        return $computed;
    }

    /**
     * Approve a withdrawal request
     */
    public function approve(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $user = Auth::user();
        
        // Check if user can approve this request
        if (!$this->canUserApproveRequest($user, $withdrawalRequest)) {
            return back()->with('error', 'You do not have permission to approve this request.');
        }

        // Check if user has already approved this request at current step
        $currentStep = $withdrawalRequest->getCurrentStepNumber();
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $existingApproval = WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
            ->where('approver_id', $user->id)
            ->where('approval_step', $currentStep)
            ->where('approver_level', $currentLevel)
            ->where('action', 'approved')
            ->first();

        if ($existingApproval) {
            return back()->with('error', 'You have already approved this request at the current step.');
        }

        try {
            DB::beginTransaction();

            // Create approval record
            WithdrawalRequestApproval::create([
                'withdrawal_request_id' => $withdrawalRequest->id,
                'approver_id' => $user->id,
                'approver_type' => 'user',
                'approver_level' => $this->getUserApproverLevel($user, $withdrawalRequest),
                'approval_step' => $currentStep,
                'action' => 'approved',
                'comment' => $request->comment ?? null,
            ]);

            // Update step-specific approval counts
            $this->updateStepApprovalCounts($withdrawalRequest, $user);

            // Check if current step is completed and move to next step
            if ($withdrawalRequest->hasApprovedCurrentStep()) {
                $withdrawalRequest->moveToNextStep();
            }

            DB::commit();

            return back()->with('success', 'Request approved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdrawal request
     */
    public function reject(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $user = Auth::user();
        
        // Check if user can approve this request (same permission as approve)
        if (!$this->canUserApproveRequest($user, $withdrawalRequest)) {
            return back()->with('error', 'You do not have permission to reject this request.');
        }

        // Check if user has already approved this request
        $existingApproval = WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
            ->where('approver_id', $user->id)
            ->first();

        if ($existingApproval) {
            return back()->with('error', 'You have already acted on this request.');
        }

        try {
            DB::beginTransaction();

            // Create rejection record
            WithdrawalRequestApproval::create([
                'withdrawal_request_id' => $withdrawalRequest->id,
                'approver_id' => $user->id,
                'approver_type' => 'user',
                'approver_level' => $this->getUserApproverLevel($user, $withdrawalRequest),
                'action' => 'rejected',
                'comment' => $request->comment ?? 'No comment provided',
            ]);

            // Reject the request (and its related request)
            $withdrawalRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->comment ?? 'Request rejected by approver',
                'rejected_at' => now(),
            ]);

            // Also reject the related request if it exists
            if ($withdrawalRequest->relatedRequest) {
                $withdrawalRequest->relatedRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Related request rejected',
                    'rejected_at' => now(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Request rejected successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can approve a withdrawal request
     */
    private function canUserApproveRequest($user, $withdrawalRequest)
    {
        // Check if request is in a state that can be approved
        if (!in_array($withdrawalRequest->status, ['pending', 'business_approved'])) {
            return false;
        }

        // Check if user has already approved at current step
        $currentStep = $withdrawalRequest->getCurrentStepNumber();
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $hasApproved = \App\Models\WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
            ->where('approver_id', $user->id)
            ->where('approval_step', $currentStep)
            ->where('approver_level', $currentLevel)
            ->where('action', 'approved')
            ->exists();

        if ($hasApproved) {
            return false;
        }

        // Use the new step-by-step approval logic
        return $withdrawalRequest->canUserApproveAtCurrentStep($user);
    }

    /**
     * Get the approver level for a user
     */
    private function getUserApproverLevel($user, $withdrawalRequest)
    {
        $withdrawalSetting = WithdrawalSetting::where('business_id', $withdrawalRequest->business_id)
            ->where('is_active', true)
            ->first();

        if ($user->business_id == 1) {
            return 'kashtre';
        } else {
            return 'business';
        }
    }

    /**
     * Update approval counts for a withdrawal request
     */
    private function updateApprovalCounts($withdrawalRequest)
    {
        $businessApprovals = $withdrawalRequest->businessApprovals()->count();
        $kashtreApprovals = $withdrawalRequest->kashtreApprovals()->count();

        $withdrawalRequest->update([
            'business_approvals_count' => $businessApprovals,
            'kashtre_approvals_count' => $kashtreApprovals,
        ]);
    }

    /**
     * Update step-specific approval counts
     */
    private function updateStepApprovalCounts($withdrawalRequest, $user)
    {
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $currentStep = $withdrawalRequest->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            $stepField = "business_step_{$currentStep}_approvals";
            $withdrawalRequest->increment($stepField);
        } elseif ($currentLevel === 'kashtre') {
            $stepField = "kashtre_step_{$currentStep}_approvals";
            $withdrawalRequest->increment($stepField);
        }

        // Also update the general approval counts
        if ($currentLevel === 'business') {
            $withdrawalRequest->increment('business_approvals_count');
        } elseif ($currentLevel === 'kashtre') {
            $withdrawalRequest->increment('kashtre_approvals_count');
        }
    }

    /**
     * Update request status based on approval counts
     */
    private function updateRequestStatus($withdrawalRequest)
    {
        $hasBusinessApproval = $withdrawalRequest->business_approvals_count >= $withdrawalRequest->required_business_approvals;
        $hasKashtreApproval = $withdrawalRequest->kashtre_approvals_count >= $withdrawalRequest->required_kashtre_approvals;

        $newStatus = $withdrawalRequest->status;

        if ($withdrawalRequest->status === 'pending' && $hasBusinessApproval) {
            $newStatus = 'business_approved';
            $withdrawalRequest->update(['business_approved_at' => now()]);
        }

        if ($newStatus === 'business_approved' && $hasKashtreApproval) {
            $newStatus = 'kashtre_approved';
            $withdrawalRequest->update(['kashtre_approved_at' => now()]);
        }

        if ($hasBusinessApproval && $hasKashtreApproval) {
            $newStatus = 'approved';
            $withdrawalRequest->update(['approved_at' => now()]);
        }

        // Update the status
        $withdrawalRequest->update(['status' => $newStatus]);

        // Also update the related request
        if ($withdrawalRequest->relatedRequest) {
            $withdrawalRequest->relatedRequest->update([
                'status' => $newStatus,
                'business_approvals_count' => $withdrawalRequest->business_approvals_count,
                'kashtre_approvals_count' => $withdrawalRequest->kashtre_approvals_count,
                'business_approved_at' => $withdrawalRequest->business_approved_at,
                'kashtre_approved_at' => $withdrawalRequest->kashtre_approved_at,
                'approved_at' => $withdrawalRequest->approved_at,
            ]);
        }
    }
}
