<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethodAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'business_id',
        'payment_method',
        'account_number',
        'account_holder_name',
        'provider',
        'balance',
        'currency',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function maturationPeriods()
    {
        return $this->hasMany(MaturationPeriod::class, 'payment_method_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(PaymentMethodAccountTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }


    public function scopeForPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    // Methods
    public function debit($amount, $reference = null, $description = null, $clientId = null, $invoiceId = null, $metadata = null)
    {
        $balanceBefore = $this->balance;
        $this->decrement('balance', $amount);
        $this->save();
        
        $balanceAfter = $this->balance;
        
        // Create transaction record
        PaymentMethodAccountTransaction::create([
            'payment_method_account_id' => $this->id,
            'business_id' => $this->business_id,
            'client_id' => $clientId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'type' => 'debit',
            'reference' => $reference ?? 'PMAT-' . time() . '-' . Str::random(8),
            'description' => $description ?? "Debit from {$this->name}",
            'status' => 'completed',
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'currency' => $this->currency,
            'transaction_for' => 'payment_received',
            'metadata' => $metadata,
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function credit($amount, $reference = null, $description = null, $clientId = null, $invoiceId = null, $metadata = null)
    {
        $balanceBefore = $this->balance;
        $this->increment('balance', $amount);
        $this->save();
        
        $balanceAfter = $this->balance;
        
        // Create transaction record
        PaymentMethodAccountTransaction::create([
            'payment_method_account_id' => $this->id,
            'business_id' => $this->business_id,
            'client_id' => $clientId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'type' => 'credit',
            'reference' => $reference ?? 'PMAT-' . time() . '-' . Str::random(8),
            'description' => $description ?? "Credit to {$this->name}",
            'status' => 'completed',
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'currency' => $this->currency,
            'transaction_for' => 'manual_adjustment',
            'metadata' => $metadata,
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    // Accessors
    public function getPaymentMethodNameAttribute()
    {
        $methodNames = [
            'insurance' => 'Insurance',
            'credit_arrangement' => 'Credit Arrangement',
            'mobile_money' => 'Mobile Money',
            'v_card' => 'V Card (Virtual Card)',
            'p_card' => 'P Card (Physical Card)',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
        ];

        return $methodNames[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }
}
