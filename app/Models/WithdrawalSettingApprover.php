<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalSettingApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'withdrawal_setting_id',
        'approver_id',
        'approver_type',
        'approver_level'
    ];

    protected $casts = [
        'approver_type' => 'string',
        'approver_level' => 'string'
    ];

    // Relationships
    public function withdrawalSetting()
    {
        return $this->belongsTo(WithdrawalSetting::class);
    }

    // Polymorphic relationship to handle both users and contractors
    public function approver()
    {
        return $this->morphTo('approver', 'approver_type', 'approver_id');
    }

    // Helper methods
    public function isBusinessLevel()
    {
        return $this->approver_level === 'business';
    }

    public function isKashtreLevel()
    {
        return $this->approver_level === 'kashtre';
    }

    public function isUserApprover()
    {
        return $this->approver_type === 'user';
    }

    public function isContractorApprover()
    {
        return $this->approver_type === 'contractor';
    }
}