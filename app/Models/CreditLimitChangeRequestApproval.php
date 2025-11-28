<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditLimitChangeRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_limit_change_request_id',
        'approver_id',
        'approval_level',
        'action',
        'comment',
    ];

    // Relationships
    public function request()
    {
        return $this->belongsTo(CreditLimitChangeRequest::class, 'credit_limit_change_request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Helper methods
    public function isPending()
    {
        return is_null($this->action);
    }

    public function isApproved()
    {
        return $this->action === 'approved';
    }

    public function isRejected()
    {
        return $this->action === 'rejected';
    }

    public function isInitiator()
    {
        return $this->approval_level === 'initiator';
    }

    public function isAuthorizer()
    {
        return $this->approval_level === 'authorizer';
    }

    public function isApprover()
    {
        return $this->approval_level === 'approver';
    }
}
