<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'client_name',
        'amount',
        'bank_name',
        'bank_account',
        'withdrawal_request_id',
        'status',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function withdrawalRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


