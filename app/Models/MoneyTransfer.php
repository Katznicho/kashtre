<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'currency',
        'status',
        'transfer_type',
        'invoice_id',
        'client_id',
        'item_id',
        'package_usage_id',
        'reference',
        'description',
        'metadata',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(MoneyAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(MoneyAccount::class, 'to_account_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function packageUsage()
    {
        return $this->belongsTo(PackageUsage::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('transfer_type', $type);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);
        return $this;
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
        return $this;
    }

    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    protected static function booted()
    {
        static::creating(function ($transfer) {
            $transfer->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
