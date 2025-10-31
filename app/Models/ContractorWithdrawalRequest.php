<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContractorWithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'contractor_profile_id',
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
        'business_approved_at' => 'datetime',
        'kashtre_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    // Relationships
    public function contractorProfile()
    {
        return $this->belongsTo(ContractorProfile::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Approval methods (similar to WithdrawalRequest)
    public function getCurrentApprovalLevel()
    {
        // If we're in Kashtre approval steps, return 'kashtre'
        if ($this->current_kashtre_step > 0 && in_array($this->status, ['business_approved', 'kashtre_approved'])) {
            return 'kashtre';
        } elseif ($this->status === 'pending' || $this->status === 'business_approved') {
            return 'business';
        } elseif ($this->status === 'kashtre_approved') {
            return 'kashtre';
        }
        return null;
    }

    public function getCurrentStepNumber()
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        if ($currentLevel === 'business') {
            return $this->current_business_step;
        } elseif ($currentLevel === 'kashtre') {
            return $this->current_kashtre_step;
        }
        return null;
    }

    public function hasApprovedCurrentStep()
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            $stepField = "business_step_{$currentStep}_approvals";
            return $this->$stepField >= 1;
        } elseif ($currentLevel === 'kashtre') {
            $stepField = "kashtre_step_{$currentStep}_approvals";
            return $this->$stepField >= 1;
        }

        return false;
    }

    public function moveToNextStep()
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            if ($currentStep < 3) {
                $this->current_business_step = $currentStep + 1;
                $this->save();
            } else {
                $this->status = 'business_approved';
                $this->business_approved_at = now();
                $this->current_kashtre_step = 1;
                $this->save();
            }
        } elseif ($currentLevel === 'kashtre') {
            if ($currentStep < 3) {
                // Step 1 or 2 approved, move to next step
                $this->current_kashtre_step = $currentStep + 1;
                $this->status = 'kashtre_approved'; // Ensure status is kashtre_approved during approval process
                $this->save();
            } else {
                // Step 3 complete, fully approved
                $this->status = 'approved';
                $this->kashtre_approved_at = now();
                $this->approved_at = now();
                $this->save();
                
                // Process the withdrawal automatically
                $this->processWithdrawal();
            }
        }
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

    public function canUserApproveAtCurrentStep($user)
    {
        $currentLevel = $this->getCurrentApprovalLevel();
        $currentStep = $this->getCurrentStepNumber();

        if (!$currentLevel || !$currentStep) {
            return false;
        }

        // Get withdrawal settings for the business
        $withdrawalSetting = WithdrawalSetting::where('business_id', $this->business_id)->first();
        if (!$withdrawalSetting) {
            return false;
        }

        if ($currentLevel === 'business') {
            $stepApprovalLevel = $this->getStepApprovalLevel($currentStep);
            return $withdrawalSetting->allBusinessApprovers()
                ->where('approver_id', $user->id)
                ->where('approval_level', $stepApprovalLevel)
                ->exists();
        } elseif ($currentLevel === 'kashtre') {
            $stepApprovalLevel = $this->getStepApprovalLevel($currentStep);
            return $withdrawalSetting->allKashtreApprovers()
                ->where('approver_id', $user->id)
                ->where('approval_level', $stepApprovalLevel)
                ->exists();
        }

        return false;
    }

    public function updateStepApprovalCounts($level, $step)
    {
        $stepField = "{$level}_step_{$step}_approvals";
        $this->increment($stepField);
        $this->save();
    }

    /**
     * Process the contractor withdrawal when fully approved
     */
    public function processWithdrawal()
    {
        try {
            $withdrawalService = app(\App\Services\ContractorWithdrawalProcessingService::class);
            return $withdrawalService->processWithdrawal($this);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to process contractor withdrawal automatically", [
                'contractor_withdrawal_request_id' => $this->id,
                'uuid' => $this->uuid,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getStatusLabel()
    {
        // For kashtre_approved status, check if all 3 steps are complete
        if ($this->status === 'kashtre_approved') {
            // If we're still in steps 1 or 2, show as pending
            if ($this->current_kashtre_step < 3) {
                return 'Pending Kashtre Approval';
            }
            // If step 3 is reached but not approved yet, still pending
            return 'Pending Kashtre Approval';
        }
        
        return match($this->status) {
            'pending' => 'Pending Approval',
            'business_approved' => 'Pending Kashtre Approval',
            'approved' => 'Fully Approved',
            'rejected' => 'Rejected',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }
}
