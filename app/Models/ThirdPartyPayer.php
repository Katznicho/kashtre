<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ThirdPartyPayer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'business_id',
        'type',
        'insurance_company_id',
        'client_id',
        'name',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'credit_limit',
        'status',
        'block_reason',
        'blocked_at',
        'blocked_by',
        'notes',
        'excluded_items',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'excluded_items' => 'array',
        'blocked_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($payer) {
            $payer->uuid = (string) Str::uuid();
        });
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Helper methods
    public function getPayerNameAttribute()
    {
        if ($this->type === 'insurance_company' && $this->insuranceCompany) {
            return $this->insuranceCompany->name;
        } elseif ($this->type === 'normal_client' && $this->client) {
            return $this->client->name;
        }
        return $this->name;
    }

    public function isInsuranceCompany()
    {
        return $this->type === 'insurance_company';
    }

    public function isNormalClient()
    {
        return $this->type === 'normal_client';
    }

    public function creditLimitChangeRequests()
    {
        return $this->morphMany(CreditLimitChangeRequest::class, 'entity');
    }

    public function accounts()
    {
        return $this->hasMany(ThirdPartyPayerAccount::class);
    }

    public function balanceHistories()
    {
        return $this->hasMany(ThirdPartyPayerBalanceHistory::class);
    }

    /**
     * Get the user who blocked this vendor.
     */
    public function blockedByUser()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Check if this vendor is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Check if this vendor is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if this vendor is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Block this vendor.
     */
    public function block(string $reason, ?int $blockedBy = null, string $status = 'blocked'): self
    {
        $this->update([
            'status' => $status,
            'block_reason' => $reason,
            'blocked_at' => now(),
            'blocked_by' => $blockedBy ?? auth()->id(),
        ]);

        return $this;
    }

    /**
     * Suspend this vendor.
     */
    public function suspend(string $reason, ?int $suspendedBy = null): self
    {
        return $this->block($reason, $suspendedBy, 'suspended');
    }

    /**
     * Reactivate this vendor.
     */
    public function reactivate(): self
    {
        $this->update([
            'status' => 'active',
            'block_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);

        return $this;
    }
}
