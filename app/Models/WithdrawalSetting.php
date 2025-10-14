<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WithdrawalSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'minimum_withdrawal_amount',
        'number_of_free_withdrawals_per_day',
        'min_business_initiators',
        'max_business_initiators',
        'min_business_authorizers',
        'max_business_authorizers',
        'min_business_approvers',
        'max_business_approvers',
        'min_kashtre_initiators',
        'max_kashtre_initiators',
        'min_kashtre_authorizers',
        'max_kashtre_authorizers',
        'min_kashtre_approvers',
        'max_kashtre_approvers',
        'withdrawal_type',
        'is_active'
    ];

    protected $casts = [
        'minimum_withdrawal_amount' => 'decimal:2',
        'number_of_free_withdrawals_per_day' => 'integer',
        'min_business_initiators' => 'integer',
        'max_business_initiators' => 'integer',
        'min_business_authorizers' => 'integer',
        'max_business_authorizers' => 'integer',
        'min_business_approvers' => 'integer',
        'max_business_approvers' => 'integer',
        'min_kashtre_initiators' => 'integer',
        'max_kashtre_initiators' => 'integer',
        'min_kashtre_authorizers' => 'integer',
        'max_kashtre_authorizers' => 'integer',
        'min_kashtre_approvers' => 'integer',
        'max_kashtre_approvers' => 'integer',
        'withdrawal_type' => 'string',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function approvers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class);
    }

    // Relationships for 3-level approval system
    public function businessInitiators()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'business')
            ->where('approval_level', 'initiator');
    }

    public function businessAuthorizers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'business')
            ->where('approval_level', 'authorizer');
    }

    public function businessApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'business')
            ->where('approval_level', 'approver');
    }

    // General relationship for all business approvers (initiators, authorizers, and approvers)
    public function allBusinessApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'business');
    }

    public function kashtreInitiators()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'kashtre')
            ->where('approval_level', 'initiator');
    }

    public function kashtreAuthorizers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'kashtre')
            ->where('approval_level', 'authorizer');
    }

    public function kashtreApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'kashtre')
            ->where('approval_level', 'approver');
    }

    // General relationship for all Kashtre approvers (initiators, authorizers, and approvers)
    public function allKashtreApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)
            ->where('approver_level', 'kashtre');
    }

    protected static function booted()
    {
        static::creating(function ($withdrawalSetting) {
            $withdrawalSetting->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
