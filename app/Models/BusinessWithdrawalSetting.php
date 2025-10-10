<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessWithdrawalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'lower_bound',
        'upper_bound',
        'charge_amount',
        'charge_type',
        'description',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'lower_bound' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function isPercentage()
    {
        return $this->charge_type === 'percentage';
    }

    public function isFixed()
    {
        return $this->charge_type === 'fixed';
    }

    // Calculate charge for a given amount
    public function calculateCharge($amount)
    {
        if ($amount < $this->lower_bound || $amount > $this->upper_bound) {
            return 0;
        }

        if ($this->isPercentage()) {
            return ($this->charge_amount / 100) * $amount;
        }

        return $this->charge_amount;
    }

    // Check if amount falls within bounds
    public function appliesToAmount($amount)
    {
        return $amount >= $this->lower_bound && $amount <= $this->upper_bound;
    }
}
