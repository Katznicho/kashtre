<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageTracking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'package_tracking';

    protected $fillable = [
        'business_id',
        'client_id',
        'invoice_id',
        'package_item_id',
        'included_item_id',
        'total_quantity',
        'used_quantity',
        'remaining_quantity',
        'valid_from',
        'valid_until',
        'status',
        'package_price',
        'item_price',
        'notes'
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'used_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'package_price' => 'decimal:2',
        'item_price' => 'decimal:2',
    ];

    // Relationships
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

    public function packageItem()
    {
        return $this->belongsTo(Item::class, 'package_item_id');
    }

    public function includedItem()
    {
        return $this->belongsTo(Item::class, 'included_item_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', now()->toDateString())
                    ->where('status', 'active');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('included_item_id', $itemId);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Methods
    public function useQuantity($quantity = 1)
    {
        if ($this->remaining_quantity >= $quantity) {
            $this->increment('used_quantity', $quantity);
            $this->decrement('remaining_quantity', $quantity);
            
            // Check if fully used
            if ($this->remaining_quantity <= 0) {
                $this->update(['status' => 'fully_used']);
            }
            
            return true;
        }
        
        return false;
    }

    public function isExpired()
    {
        return $this->valid_until < now()->toDateString();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function hasRemainingQuantity()
    {
        return $this->remaining_quantity > 0;
    }

    public function getUsagePercentageAttribute()
    {
        if ($this->total_quantity > 0) {
            return round(($this->used_quantity / $this->total_quantity) * 100, 2);
        }
        return 0;
    }

    protected static function booted()
    {
        static::creating(function ($tracking) {
            $tracking->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
