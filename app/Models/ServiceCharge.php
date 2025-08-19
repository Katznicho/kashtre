<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Business;
use App\Models\Branch;
use App\Models\ServicePoint;
use App\Models\User;

class ServiceCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'amount',
        'upper_bound',
        'lower_bound',
        'type',
        'is_active',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'lower_bound' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the business that owns the service charge.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user who created the service charge.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the entity that this service charge applies to.
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by active service charges.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeForEntityType($query, $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Get the formatted amount with type.
     */
    public function getFormattedAmountAttribute(): string
    {
        if ($this->type === 'percentage') {
            return $this->amount . '%';
        }
        
        return 'UGX ' . number_format($this->amount, 2);
    }

    /**
     * Get the entity name.
     */
    public function getEntityNameAttribute(): string
    {
        switch ($this->entity_type) {
            case 'business':
                return Business::find($this->entity_id)?->name ?? 'Unknown Business';
            case 'branch':
                return Branch::find($this->entity_id)?->name ?? 'Unknown Branch';
            case 'service_point':
                return ServicePoint::find($this->entity_id)?->name ?? 'Unknown Service Point';
            default:
                return 'Unknown Entity';
        }
    }
}
