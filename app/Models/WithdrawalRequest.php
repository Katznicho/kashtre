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
        'business_approved_at',
        'kashtre_approved_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withdrawal_charge' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'business_approvals_count' => 'integer',
        'kashtre_approvals_count' => 'integer',
        'required_business_approvals' => 'integer',
        'required_kashtre_approvals' => 'integer',
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
}
