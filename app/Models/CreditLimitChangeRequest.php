<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreditLimitChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'business_id',
        'initiated_by',
        'entity_type',
        'entity_id',
        'current_credit_limit',
        'requested_credit_limit',
        'reason',
        'status',
        'current_step',
        'initiated_by_user_id',
        'initiated_at',
        'authorized_by_user_id',
        'authorized_at',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'current_credit_limit' => 'decimal:2',
        'requested_credit_limit' => 'decimal:2',
        'initiated_at' => 'datetime',
        'authorized_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'current_step' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($request) {
            if (empty($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function initiatedByUser()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function authorizedByUser()
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function approvals()
    {
        return $this->hasMany(CreditLimitChangeRequestApproval::class);
    }

    public function entity()
    {
        if ($this->entity_type === 'client') {
            return Client::find($this->entity_id);
        } elseif ($this->entity_type === 'third_party_payer') {
            return ThirdPartyPayer::find($this->entity_id);
        }
        return null;
    }

    // Helper methods
    public function getEntityAttribute()
    {
        if ($this->entity_type === 'client') {
            return Client::find($this->entity_id);
        } elseif ($this->entity_type === 'third_party_payer') {
            return ThirdPartyPayer::find($this->entity_id);
        }
        return null;
    }

    public function getEntityNameAttribute()
    {
        $entity = $this->entity;
        if ($entity) {
            if ($this->entity_type === 'client') {
                return $entity->name ?? 'Unknown Client';
            } elseif ($this->entity_type === 'third_party_payer') {
                return $entity->name ?? 'Unknown Payer';
            }
        }
        return 'Unknown';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInitiated()
    {
        return $this->status === 'initiated';
    }

    public function isAuthorized()
    {
        return $this->status === 'authorized';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function canBeAuthorized()
    {
        return $this->status === 'initiated' && $this->current_step === 2;
    }

    public function canBeApproved()
    {
        return $this->status === 'authorized' && $this->current_step === 3;
    }

    public function getPendingAuthorizers()
    {
        return $this->approvals()
            ->where('approval_level', 'authorizer')
            ->whereNull('action')
            ->get();
    }

    public function getPendingApprovers()
    {
        return $this->approvals()
            ->where('approval_level', 'approver')
            ->whereNull('action')
            ->get();
    }

    public function hasAllAuthorizersApproved()
    {
        // Check if at least one authorizer has approved (changed from requiring all)
        return $this->approvals()
            ->where('approval_level', 'authorizer')
            ->where('action', 'approved')
            ->exists();
    }

    public function hasAllApproversApproved()
    {
        // Check if at least one approver has approved (changed from requiring all)
        return $this->approvals()
            ->where('approval_level', 'approver')
            ->where('action', 'approved')
            ->exists();
    }

    public function hasAnyRejection()
    {
        return $this->approvals()
            ->where('action', 'rejected')
            ->exists();
    }
}
