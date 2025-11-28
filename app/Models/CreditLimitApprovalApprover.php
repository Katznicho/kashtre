<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditLimitApprovalApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'approver_id',
        'approver_type',
        'approval_level',
    ];

    protected $casts = [
        'approver_type' => 'string',
        'approval_level' => 'string',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Helper methods
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
