<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorServiceCharge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'contractor_profile_id',
        'amount',
        'upper_bound',
        'lower_bound',
        'type',
        'description',
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
     * Validation rules for the model.
     */
    public static $rules = [
        'contractor_profile_id' => 'required|integer|exists:contractor_profiles,id',
        'amount' => 'required|numeric|min:0',
        'upper_bound' => 'nullable|numeric|min:0',
        'lower_bound' => 'nullable|numeric|min:0',
        'type' => 'required|in:fixed,percentage',
        'description' => 'nullable|string|max:500',
        'is_active' => 'boolean',
        'business_id' => 'required|integer|exists:businesses,id',
        'created_by' => 'required|integer|exists:users,id',
    ];

    /**
     * Custom validation messages.
     */
    public static $messages = [
        'contractor_profile_id.required' => 'Contractor profile is required.',
        'contractor_profile_id.integer' => 'Contractor profile ID must be a valid integer.',
        'contractor_profile_id.exists' => 'Selected contractor profile does not exist.',
        'amount.required' => 'Amount is required.',
        'amount.numeric' => 'Amount must be a valid number.',
        'amount.min' => 'Amount must be greater than or equal to 0.',
        'upper_bound.numeric' => 'Upper bound must be a valid number.',
        'upper_bound.min' => 'Upper bound must be greater than or equal to 0.',
        'lower_bound.numeric' => 'Lower bound must be a valid number.',
        'lower_bound.min' => 'Lower bound must be greater than or equal to 0.',
        'type.required' => 'Type is required.',
        'type.in' => 'Type must be either fixed or percentage.',
        'description.string' => 'Description must be a valid string.',
        'description.max' => 'Description cannot exceed 500 characters.',
        'business_id.required' => 'Business ID is required.',
        'business_id.integer' => 'Business ID must be a valid integer.',
        'business_id.exists' => 'Selected business does not exist.',
        'created_by.required' => 'Created by user is required.',
        'created_by.integer' => 'Created by user ID must be a valid integer.',
        'created_by.exists' => 'Selected user does not exist.',
    ];

    protected static function booted()
    {
        static::creating(function ($contractorServiceCharge) {
            $contractorServiceCharge->uuid = (string) Str::uuid();
        });

        static::saving(function ($contractorServiceCharge) {
            // Validate bounds relationship
            if ($contractorServiceCharge->upper_bound !== null && $contractorServiceCharge->lower_bound !== null) {
                if ($contractorServiceCharge->upper_bound <= $contractorServiceCharge->lower_bound) {
                    throw new \InvalidArgumentException('Upper bound must be greater than lower bound.');
                }
            }
            
            // Validate percentage limits
            if ($contractorServiceCharge->type === 'percentage' && $contractorServiceCharge->amount > 100) {
                throw new \InvalidArgumentException('Percentage amount cannot exceed 100%.');
            }
        });
    }

    /**
     * Get the contractor profile that owns the service charge.
     */
    public function contractorProfile(): BelongsTo
    {
        return $this->belongsTo(ContractorProfile::class);
    }

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
     * Scope to filter by contractor profile.
     */
    public function scopeForContractor($query, $contractorProfileId)
    {
        return $query->where('contractor_profile_id', $contractorProfileId);
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
     * Get the contractor name.
     */
    public function getContractorNameAttribute(): string
    {
        return $this->contractorProfile->user->name ?? 'Unknown Contractor';
    }

    /**
     * Get the route key name.
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
