<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentMethodAccountTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_method_account_id',
        'business_id',
        'client_id',
        'invoice_id',
        'amount',
        'type',
        'reference',
        'external_reference',
        'description',
        'status',
        'balance_before',
        'balance_after',
        'currency',
        'transaction_for',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
            if (empty($transaction->reference)) {
                $transaction->reference = 'PMAT-' . time() . '-' . Str::random(8);
            }
        });
    }

    // Relationships
    public function paymentMethodAccount()
    {
        return $this->belongsTo(PaymentMethodAccount::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('payment_method_account_id', $accountId);
    }
}
