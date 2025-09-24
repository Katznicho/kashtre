<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaturationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'payment_method',
        'maturation_days',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'maturation_days' => 'integer',
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

    // Accessors
    public function getPaymentMethodNameAttribute()
    {
        $methodNames = [
            'insurance' => '🛡️ Insurance',
            'credit_arrangement' => '💳 Credit Arrangement',
            'mobile_money' => '📱 MM (Mobile Money)',
            'v_card' => '💳 V Card (Virtual Card)',
            'p_card' => '💳 P Card (Physical Card)',
            'bank_transfer' => '🏦 Bank Transfer',
            'cash' => '💵 Cash',
        ];

        return $methodNames[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    public function getFormattedMaturationPeriodAttribute()
    {
        return $this->maturation_days . ' day' . ($this->maturation_days > 1 ? 's' : '');
    }
}
