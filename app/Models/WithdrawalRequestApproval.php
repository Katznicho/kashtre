<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'withdrawal_request_id',
        'approver_id',
        'approver_type',
        'approver_level',
        'approval_step',
        'action',
        'comment',
    ];

    // Relationships
    public function withdrawalRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
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

    public function isApproved()
    {
        return $this->action === 'approved';
    }

    public function isRejected()
    {
        return $this->action === 'rejected';
    }
}
