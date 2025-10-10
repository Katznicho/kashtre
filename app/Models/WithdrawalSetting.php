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
        'min_business_approvers',
        'min_kashtre_approvers',
        'withdrawal_type',
        'is_active'
    ];

    protected $casts = [
        'minimum_withdrawal_amount' => 'decimal:2',
        'number_of_free_withdrawals_per_day' => 'integer',
        'min_business_approvers' => 'integer',
        'min_kashtre_approvers' => 'integer',
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

    public function businessApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)->where('approver_level', 'business');
    }

    public function kashtreApprovers()
    {
        return $this->hasMany(WithdrawalSettingApprover::class)->where('approver_level', 'kashtre');
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
