<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientVendor extends Model
{
    protected $fillable = [
        'client_id',
        'third_party_payer_id',
        'policy_number',
        'policy_verified',
        'is_open_enrollment',
        'priority',
        'deductible_amount',
        'copay_amount',
        'coinsurance_percentage',
        'copay_max_limit',
        'copay_contributes_to_deductible',
        'coinsurance_contributes_to_deductible',
        'excluded_items',
        'status',
        'notes',
    ];

    protected $casts = [
        'excluded_items' => 'array',
        'policy_verified' => 'boolean',
        'is_open_enrollment' => 'boolean',
        'copay_contributes_to_deductible' => 'boolean',
        'coinsurance_contributes_to_deductible' => 'boolean',
        'deductible_amount' => 'decimal:2',
        'copay_amount' => 'decimal:2',
        'copay_max_limit' => 'decimal:2',
        'coinsurance_percentage' => 'decimal:2',
    ];

    /**
     * Get the client that this vendor record belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the vendor (third-party payer).
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyPayer::class, 'third_party_payer_id');
    }

    /**
     * Check if vendor is suspended or blocked.
     */
    public function isVendorBlocked(): bool
    {
        return $this->status === 'suspended' || $this->status === 'blocked';
    }
}
