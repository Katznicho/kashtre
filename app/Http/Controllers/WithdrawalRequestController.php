<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\WithdrawalRequestApproval;
use App\Models\WithdrawalSetting;
use App\Models\BusinessWithdrawalSetting;
use App\Models\Business;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\MoneyTransfer;
use App\Models\User;
use App\Services\MoneyTrackingService;
use App\Notifications\WithdrawalRequestNotification;
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
        
        // Calculate current account balance from BusinessBalanceHistory (source of truth)
        // Only consider business_account records, not suspense accounts
        $businessAccount = \App\Models\MoneyAccount::where('business_id', $business->id)
            ->where('type', 'business_account')
            ->first();
        
        $currentBalance = 0;
        if ($businessAccount) {
            $businessBalanceHistories = \App\Models\BusinessBalanceHistory::where('business_id', $business->id)
                ->where('money_account_id', $businessAccount->id)
                ->get();
            
            $totalCredits = $businessBalanceHistories->where('type', 'credit')->sum('amount');
            $totalDebits = $businessBalanceHistories->where('type', 'debit')->sum('amount');
            $currentBalance = $totalCredits - $totalDebits;
        }

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
            
            // Total to be held in suspense is amount + charge
            $totalDeduction = $request->amount + $withdrawalCharge;

            // Check if business has sufficient balance
            $businessAccount = MoneyAccount::where('business_id', $user->business_id)
                ->where('type', 'business_account')
                ->first();

            if (!$businessAccount) {
                throw new \Exception('Business account not found. Please contact administrator.');
            }

            // Calculate current balance from BusinessBalanceHistory
            $businessBalanceHistories = BusinessBalanceHistory::where('business_id', $user->business_id)
                ->where('money_account_id', $businessAccount->id)
                ->get();
            
            $totalCredits = $businessBalanceHistories->where('type', 'credit')->sum('amount');
            $totalDebits = $businessBalanceHistories->where('type', 'debit')->sum('amount');
            $currentBalance = $totalCredits - $totalDebits;

            if ($currentBalance < $totalDeduction) {
                throw new \Exception('Insufficient balance. Your account balance (' . number_format($currentBalance, 2) . ' UGX) is insufficient to cover the total deduction (' . number_format($totalDeduction, 2) . ' UGX). Please reduce the withdrawal amount.');
            }

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

            // Hold funds in withdrawal suspense account
            $moneyTrackingService = new MoneyTrackingService();
            $withdrawalSuspenseAccount = $moneyTrackingService->getOrCreateWithdrawalSuspenseAccount($user->business);

            // Debit from business_account
            BusinessBalanceHistory::recordChange(
                $user->business_id,
                $businessAccount->id,
                $totalDeduction,
                'debit',
                "Withdrawal Request Hold - {$withdrawalRequest->uuid}",
                WithdrawalRequest::class,
                $withdrawalRequest->id,
                ['withdrawal_request_uuid' => $withdrawalRequest->uuid],
                $user->id
            );

            // Credit to withdrawal suspense account
            BusinessBalanceHistory::recordChange(
                $user->business_id,
                $withdrawalSuspenseAccount->id,
                $totalDeduction,
                'credit',
                "Withdrawal Request Hold - {$withdrawalRequest->uuid}",
                WithdrawalRequest::class,
                $withdrawalRequest->id,
                ['withdrawal_request_uuid' => $withdrawalRequest->uuid],
                $user->id
            );

            // Create MoneyTransfer record for consistency with other suspense accounts (same table format)
            MoneyTransfer::create([
                'business_id' => $user->business_id,
                'from_account_id' => $businessAccount->id,
                'to_account_id' => $withdrawalSuspenseAccount->id,
                'amount' => $totalDeduction,
                'currency' => 'UGX',
                'status' => 'completed',
                'type' => 'credit',
                'transfer_type' => 'withdrawal_hold',
                'description' => "Withdrawal Request Hold - {$withdrawalRequest->uuid}",
                'source' => $businessAccount->name,
                'destination' => $withdrawalSuspenseAccount->name,
                'processed_at' => now(),
            ]);

            Log::info('WR Funds held in suspense', [
                'withdrawal_request_id' => $withdrawalRequest->id,
                'total_deduction' => $totalDeduction,
                'business_account_id' => $businessAccount->id,
                'withdrawal_suspense_account_id' => $withdrawalSuspenseAccount->id,
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
                    // Check if approval already exists to avoid duplicate entry error
                    $existingApproval = WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
                        ->where('approver_id', $user->id)
                        ->first();

                    if (!$existingApproval) {
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
                            // Notify approvers at step 2
                            $this->notifyNextStepApprovers($withdrawalRequest);
                        } else {
                            // Notify approvers at step 2 (authorizers) that a new request needs approval
                            $this->notifyNextStepApprovers($withdrawalRequest);
                        }
                    } else {
                        // Notify approvers at step 2 (authorizers) that a new request needs approval
                        $this->notifyNextStepApprovers($withdrawalRequest);
                    }
                } else {
                    // Notify approvers at step 2 (authorizers) that a new request needs approval
                    $this->notifyNextStepApprovers($withdrawalRequest);
                }
            } else {
                // Notify approvers at step 2 (authorizers) that a new request needs approval
                $this->notifyNextStepApprovers($withdrawalRequest);
            }

            // Notify requester that request was created
            $requester = $withdrawalRequest->requester;
            if ($requester) {
                $requester->notify(new WithdrawalRequestNotification(
                    $withdrawalRequest,
                    'created',
                    "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been created and is pending approval."
                ));
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
            $movedToNextStep = false;
            if ($withdrawalRequest->hasApprovedCurrentStep()) {
                $oldStatus = $withdrawalRequest->status;
                $withdrawalRequest->moveToNextStep();
                // Refresh after moveToNextStep to get updated status
                $withdrawalRequest->refresh();
                $movedToNextStep = true;
                
                // Notify approvers at next step
                $this->notifyNextStepApprovers($withdrawalRequest);
                
                // If moved from business_approved to approved, notify requester
                if ($withdrawalRequest->status === 'approved') {
                    // Update the description in BusinessBalanceHistory from "Hold" to "Accepted"
                    $businessAccount = MoneyAccount::where('business_id', $withdrawalRequest->business_id)
                        ->where('type', 'business_account')
                        ->first();
                    
                    if ($businessAccount) {
                        BusinessBalanceHistory::where('business_id', $withdrawalRequest->business_id)
                            ->where('money_account_id', $businessAccount->id)
                            ->where('reference_type', WithdrawalRequest::class)
                            ->where('reference_id', $withdrawalRequest->id)
                            ->where('description', 'like', '%Withdrawal Request Hold%')
                            ->update([
                                'description' => "Withdrawal Request Accepted - {$withdrawalRequest->uuid}"
                            ]);
                    }
                    
                    // Verify bank schedule was created
                    $bankSchedule = \App\Models\BankSchedule::where('withdrawal_request_id', $withdrawalRequest->id)->first();
                    if (!$bankSchedule) {
                        // Bank schedule was not created, try to create it again
                        \Log::warning('Bank schedule not found after approval, attempting to create', [
                            'withdrawal_request_id' => $withdrawalRequest->id
                        ]);
                        try {
                            $withdrawalRequest->createBankSchedule();
                        } catch (\Exception $e) {
                            \Log::error('Failed to create bank schedule after approval', [
                                'withdrawal_request_id' => $withdrawalRequest->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $requester = $withdrawalRequest->requester;
                    if ($requester) {
                        $requester->notify(new WithdrawalRequestNotification(
                            $withdrawalRequest,
                            'fully_approved',
                            "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been fully approved and will be processed."
                        ));
                    }
                } elseif ($oldStatus === 'pending' && $withdrawalRequest->status === 'business_approved') {
                    // Business approval complete, notify requester
                    $requester = $withdrawalRequest->requester;
                    if ($requester) {
                        $requester->notify(new WithdrawalRequestNotification(
                            $withdrawalRequest,
                            'step_completed',
                            "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been approved by business and is now pending Kashtre approval."
                        ));
                    }
                }
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

            // Release funds from withdrawal suspense account back to business account
            $business = $withdrawalRequest->business;
            $totalDeduction = $withdrawalRequest->amount + $withdrawalRequest->withdrawal_charge;

            $businessAccount = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'business_account')
                ->first();

            if ($businessAccount) {
                $moneyTrackingService = new MoneyTrackingService();
                $withdrawalSuspenseAccount = $moneyTrackingService->getOrCreateWithdrawalSuspenseAccount($business);

                // Debit from withdrawal_suspense_account (create debit entry in suspense account history)
                BusinessBalanceHistory::recordChange(
                    $business->id,
                    $withdrawalSuspenseAccount->id,
                    $totalDeduction,
                    'debit',
                    "Withdrawal Request Rejected - {$withdrawalRequest->uuid}",
                    WithdrawalRequest::class,
                    $withdrawalRequest->id,
                    ['withdrawal_request_uuid' => $withdrawalRequest->uuid],
                    $user->id
                );

                // Credit back to business_account
                BusinessBalanceHistory::recordChange(
                    $business->id,
                    $businessAccount->id,
                    $totalDeduction,
                    'credit',
                    "Withdrawal Request Rejected - {$withdrawalRequest->uuid}",
                    WithdrawalRequest::class,
                    $withdrawalRequest->id,
                    ['withdrawal_request_uuid' => $withdrawalRequest->uuid],
                    $user->id
                );

                // Create MoneyTransfer record for consistency with other suspense accounts (same table format)
                // This represents the return of funds from withdrawal suspense back to business account
                MoneyTransfer::create([
                    'business_id' => $business->id,
                    'from_account_id' => $withdrawalSuspenseAccount->id,
                    'to_account_id' => $businessAccount->id,
                    'amount' => $totalDeduction,
                    'currency' => 'UGX',
                    'status' => 'completed',
                    'type' => 'credit',
                    'transfer_type' => 'withdrawal_rejected',
                    'description' => "Withdrawal Request Rejected - {$withdrawalRequest->uuid}",
                    'source' => $withdrawalSuspenseAccount->name,
                    'destination' => $businessAccount->name,
                    'processed_at' => now(),
                ]);

                Log::info('WR Funds released from suspense on rejection', [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'total_deduction' => $totalDeduction,
                    'business_account_id' => $businessAccount->id,
                    'withdrawal_suspense_account_id' => $withdrawalSuspenseAccount->id,
                ]);
            }

            // Notify requester that request was rejected
            $requester = $withdrawalRequest->requester;
            if ($requester) {
                $requester->notify(new WithdrawalRequestNotification(
                    $withdrawalRequest,
                    'rejected',
                    "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been rejected. Reason: " . ($request->comment ?? 'No reason provided')
                ));
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

    /**
     * Notify approvers at the next step that they need to approve
     */
    private function notifyNextStepApprovers(WithdrawalRequest $withdrawalRequest)
    {
        $withdrawalSetting = WithdrawalSetting::where('business_id', $withdrawalRequest->business_id)
            ->where('withdrawal_type', $withdrawalRequest->withdrawal_type)
            ->where('is_active', true)
            ->first();

        if (!$withdrawalSetting) {
            return;
        }

        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $currentStep = $withdrawalRequest->getCurrentStepNumber();

        // Get approvers for the current step
        $approvers = collect();
        
        if ($currentLevel === 'business') {
            $stepApprovalLevel = $withdrawalRequest->getStepApprovalLevel($currentStep);
            $approvers = $withdrawalSetting->allBusinessApprovers()
                ->where('approver_type', 'user')
                ->where('approval_level', $stepApprovalLevel)
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();
        } elseif ($currentLevel === 'kashtre') {
            $stepApprovalLevel = $withdrawalRequest->getStepApprovalLevel($currentStep);
            $approvers = $withdrawalSetting->allKashtreApprovers()
                ->where('approver_type', 'user')
                ->where('approval_level', $stepApprovalLevel)
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();
        }

        // Send notification to each approver
        foreach ($approvers as $approver) {
            if ($approver) {
                $stepName = ucfirst($stepApprovalLevel);
                $levelName = ucfirst($currentLevel);
                $approver->notify(new WithdrawalRequestNotification(
                    $withdrawalRequest,
                    'pending_approval',
                    "A withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX from {$withdrawalRequest->business->name} requires your approval at {$levelName} {$stepName} level."
                ));
            }
        }
    }
}
