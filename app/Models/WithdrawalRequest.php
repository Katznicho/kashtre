<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'business_id',
        'requested_by',
        'amount',
        'withdrawal_charge',
        'net_amount',
        'withdrawal_type',
        'status',
        'reason',
        'rejection_reason',
        'account_number',
        'account_name',
        'bank_name',
        'mobile_money_number',
        'payment_method',
        'business_approvals_count',
        'kashtre_approvals_count',
        'required_business_approvals',
        'required_kashtre_approvals',
        'current_business_step',
        'current_kashtre_step',
        'business_step_1_approvals',
        'business_step_2_approvals',
        'business_step_3_approvals',
        'kashtre_step_1_approvals',
        'kashtre_step_2_approvals',
        'kashtre_step_3_approvals',
        'business_approved_at',
        'kashtre_approved_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'processed_by',
        'transaction_reference',
        'request_type',
        'related_request_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withdrawal_charge' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'business_approvals_count' => 'integer',
        'kashtre_approvals_count' => 'integer',
        'required_business_approvals' => 'integer',
        'required_kashtre_approvals' => 'integer',
        'current_business_step' => 'integer',
        'current_kashtre_step' => 'integer',
        'business_step_1_approvals' => 'integer',
        'business_step_2_approvals' => 'integer',
        'business_step_3_approvals' => 'integer',
        'kashtre_step_1_approvals' => 'integer',
        'kashtre_step_2_approvals' => 'integer',
        'kashtre_step_3_approvals' => 'integer',
        'business_approved_at' => 'datetime',
        'kashtre_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($withdrawalRequest) {
            $withdrawalRequest->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvals()
    {
        return $this->hasMany(WithdrawalRequestApproval::class);
    }

    public function businessApprovals()
    {
        return $this->hasMany(WithdrawalRequestApproval::class)
            ->where('approver_level', 'business');
    }

    public function kashtreApprovals()
    {
        return $this->hasMany(WithdrawalRequestApproval::class)
            ->where('approver_level', 'kashtre');
    }

    // Relationship to the related withdrawal request
    public function relatedRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class, 'related_request_id');
    }

    // Relationship to all requests with the same transaction reference
    public function transactionRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'transaction_reference', 'transaction_reference')
            ->where('id', '!=', $this->id);
    }

    // Status Check Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isBusinessApproved()
    {
        return $this->status === 'business_approved';
    }

    public function isKashtreApproved()
    {
        return $this->status === 'kashtre_approved';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    // Approval Check Methods
    public function hasBusinessApproval()
    {
        return $this->business_approvals_count >= $this->required_business_approvals;
    }

    public function hasKashtreApproval()
    {
        return $this->kashtre_approvals_count >= $this->required_kashtre_approvals;
    }

    public function hasFullApproval()
    {
        return $this->hasBusinessApproval() && $this->hasKashtreApproval();
    }

    public function canBeApprovedBy(User $user, $level)
    {
        // Check if user has already approved
        $hasApproved = $this->approvals()
            ->where('approver_id', $user->id)
            ->where('approver_level', $level)
            ->exists();

        if ($hasApproved) {
            return false;
        }

        // Check if user is in the approved list for this level
        $withdrawalSetting = WithdrawalSetting::where('business_id', $this->business_id)->first();
        
        if (!$withdrawalSetting) {
            return false;
        }

        if ($level === 'business') {
            return $withdrawalSetting->businessApprovers()
                ->where('approver_id', $user->id)
                ->where('approver_type', 'user')
                ->exists();
        } else {
            return $withdrawalSetting->kashtreApprovers()
                ->where('approver_id', $user->id)
                ->where('approver_type', 'user')
                ->exists();
        }
    }

    // Step-by-Step Approval Methods
    public function getCurrentApprovalStep()
    {
        if ($this->status === 'pending') {
            return "Business Step {$this->current_business_step}";
        } elseif ($this->status === 'business_approved') {
            return "Kashtre Step {$this->current_kashtre_step}";
        }
        return 'Completed';
    }

    public function getCurrentApprovalLevel()
    {
        if ($this->status === 'pending') {
            return 'business';
        } elseif ($this->status === 'business_approved') {
            return 'kashtre';
        }
        return 'completed';
    }

    public function getCurrentStepNumber()
    {
        if ($this->status === 'pending') {
            return $this->current_business_step;
        } elseif ($this->status === 'business_approved') {
            return $this->current_kashtre_step;
        }
        return 0;
    }

    public function canUserApproveAtCurrentStep(User $user)
    {
        $withdrawalSetting = WithdrawalSetting::where('business_id', $this->business_id)->first();
        
        if (!$withdrawalSetting) {
            return false;
        }

        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            // Check if user is assigned to the current business step
            $stepApprovalLevel = $this->getStepApprovalLevel($currentStep);
            return $withdrawalSetting->allBusinessApprovers()
                ->where('approver_id', $user->id)
                ->where('approver_type', 'user')
                ->where('approval_level', $stepApprovalLevel)
                ->exists();
        } elseif ($currentLevel === 'kashtre') {
            // Check if user is assigned to the current kashtre step
            $stepApprovalLevel = $this->getStepApprovalLevel($currentStep);
            return $withdrawalSetting->allKashtreApprovers()
                ->where('approver_id', $user->id)
                ->where('approver_type', 'user')
                ->where('approval_level', $stepApprovalLevel)
                ->exists();
        }

        return false;
    }

    public function getStepApprovalLevel($step)
    {
        return match($step) {
            1 => 'initiator',
            2 => 'authorizer',
            3 => 'approver',
            default => 'initiator'
        };
    }

    public function hasApprovedCurrentStep()
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            $stepField = "business_step_{$currentStep}_approvals";
            return $this->$stepField >= 1; // Only need 1 approval per step
        } elseif ($currentLevel === 'kashtre') {
            $stepField = "kashtre_step_{$currentStep}_approvals";
            return $this->$stepField >= 1; // Only need 1 approval per step
        }

        return false;
    }

    public function moveToNextStep()
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            if ($currentStep < 3) {
                // Move to next business step
                $this->current_business_step = $currentStep + 1;
                $this->save();
            } else {
                // All business steps completed, move to kashtre
                $this->status = 'business_approved';
                $this->business_approved_at = now();
                $this->current_kashtre_step = 1;
                $this->save();
            }
        } elseif ($currentLevel === 'kashtre') {
            if ($currentStep < 3) {
                // Move to next kashtre step
                $this->current_kashtre_step = $currentStep + 1;
                $this->save();
            } else {
                // All kashtre steps completed, fully approved
                $this->status = 'approved';
                $this->kashtre_approved_at = now();
                $this->approved_at = now();
                $this->save();
                
                // Instead of processing withdrawal immediately, create a bank schedule
                // Money will move when the bank schedule is processed
                try {
                    $this->createBankSchedule();
                } catch (\Exception $e) {
                    \Log::error("Error creating bank schedule in moveToNextStep", [
                        'withdrawal_request_id' => $this->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Re-throw to ensure the error is handled upstream
                    throw $e;
                }
            }
        }
    }

    public function getApprovalProgress()
    {
        $businessProgress = 0;
        $kashtreProgress = 0;

        // Calculate business progress
        if ($this->business_step_1_approvals >= 1) $businessProgress++;
        if ($this->business_step_2_approvals >= 1) $businessProgress++;
        if ($this->business_step_3_approvals >= 1) $businessProgress++;

        // Calculate kashtre progress
        if ($this->kashtre_step_1_approvals >= 1) $kashtreProgress++;
        if ($this->kashtre_step_2_approvals >= 1) $kashtreProgress++;
        if ($this->kashtre_step_3_approvals >= 1) $kashtreProgress++;

        return [
            'business' => [
                'completed' => $businessProgress,
                'total' => 3,
                'percentage' => round(($businessProgress / 3) * 100, 1)
            ],
            'kashtre' => [
                'completed' => $kashtreProgress,
                'total' => 3,
                'percentage' => round(($kashtreProgress / 3) * 100, 1)
            ],
            'overall' => [
                'completed' => $businessProgress + $kashtreProgress,
                'total' => 6,
                'percentage' => round((($businessProgress + $kashtreProgress) / 6) * 100, 1)
            ]
        ];
    }

    // Type Check Methods
    public function isRegular()
    {
        return $this->withdrawal_type === 'regular';
    }

    public function isExpress()
    {
        return $this->withdrawal_type === 'express';
    }

    // Payment Method Check
    public function isBankTransfer()
    {
        return $this->payment_method === 'bank_transfer';
    }

    public function isMobileMoney()
    {
        return $this->payment_method === 'mobile_money';
    }

    // Status Badge Color Helper
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'business_approved' => 'info',
            'kashtre_approved' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'gray',
        };
    }

    // Get formatted status
    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'business_approved' => 'Business Approved',
            'kashtre_approved' => 'Kashtre Approved',
            'approved' => 'Fully Approved',
            'rejected' => 'Rejected',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Process the withdrawal when fully approved (legacy - now handled by bank schedule)
     */
    public function processWithdrawal()
    {
        try {
            $withdrawalService = app(\App\Services\WithdrawalProcessingService::class);
            return $withdrawalService->processWithdrawal($this);
        } catch (\Exception $e) {
            \Log::error("Failed to process withdrawal automatically", [
                'withdrawal_request_id' => $this->id,
                'uuid' => $this->uuid,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a bank schedule when withdrawal request is fully approved
     * This replaces automatic money movement - money will move when bank schedule is processed
     */
    public function createBankSchedule()
    {
        try {
            // Create bank schedule for ALL payment methods (bank transfer and mobile money)
            // Money will only move when bank schedule is marked as done

            // Check if bank schedule already exists
            $existingSchedule = \App\Models\BankSchedule::where('withdrawal_request_id', $this->id)->first();
            if ($existingSchedule) {
                \Log::info("Bank schedule already exists for withdrawal request", [
                    'withdrawal_request_id' => $this->id,
                    'bank_schedule_id' => $existingSchedule->id
                ]);
                return $existingSchedule;
            }

            // Get the requester name
            $requester = $this->requester;
            $clientName = $this->account_name ?? ($requester ? $requester->name : 'Unknown');

            // Get the last Kashtre approver (who just approved step 3)
            $lastApproval = $this->kashtreApprovals()
                ->where('approval_step', 3)
                ->where('action', 'approved')
                ->latest()
                ->first();
            $createdBy = $lastApproval ? $lastApproval->approver_id : (auth()->id() ?? 1);

            // Create bank schedule
            $bankSchedule = \App\Models\BankSchedule::create([
                'business_id' => $this->business_id,
                'client_name' => $clientName,
                'amount' => $this->net_amount, // Use net amount (amount after charge)
                'withdrawal_charge' => $this->withdrawal_charge, // Include withdrawal charge
                'bank_name' => $this->bank_name ?? ($this->payment_method === 'mobile_money' ? 'Mobile Money' : 'Not specified'),
                'bank_account' => $this->account_number ?? $this->mobile_money_number ?? 'Not specified',
                'withdrawal_request_id' => $this->id,
                'status' => 'pending',
                'reference_id' => $this->uuid,
                'created_by' => $createdBy, // Use the last Kashtre approver
            ]);

            \Log::info("Bank schedule created for withdrawal request", [
                'withdrawal_request_id' => $this->id,
                'bank_schedule_id' => $bankSchedule->id,
                'amount' => $bankSchedule->amount,
                'payment_method' => $this->payment_method,
                'business_id' => $this->business_id
            ]);

            return $bankSchedule;
        } catch (\Exception $e) {
            \Log::error("Failed to create bank schedule for withdrawal request", [
                'withdrawal_request_id' => $this->id,
                'uuid' => $this->uuid,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
