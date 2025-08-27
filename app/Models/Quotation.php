<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'invoice_id',
        'client_id',
        'business_id',
        'branch_id',
        'created_by',
        'client_name',
        'client_phone',
        'payment_phone',
        'visit_id',
        'items',
        'subtotal',
        'package_adjustment',
        'account_balance_adjustment',
        'service_charge',
        'total_amount',
        'payment_methods',
        'notes',
        'status',
        'valid_until',
        'generated_at',
    ];

    protected $casts = [
        'items' => 'array',
        'payment_methods' => 'array',
        'subtotal' => 'decimal:2',
        'package_adjustment' => 'decimal:2',
        'account_balance_adjustment' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'valid_until' => 'datetime',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public static function generateQuotationNumber($businessId)
    {
        $prefix = 'QT';
        $year = date('Y');
        $month = date('m');
        
        // Get the last quotation number for this business and month
        $lastQuotation = self::where('business_id', $businessId)
            ->where('quotation_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('quotation_number', 'desc')
            ->first();
        
        if ($lastQuotation) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastQuotation->quotation_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function markAsAccepted()
    {
        $this->update([
            'status' => 'accepted',
        ]);
    }

    public function markAsRejected()
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    // Accessors
    public function getFormattedQuotationNumberAttribute()
    {
        return $this->quotation_number;
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'UGX ' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-gray-100 text-gray-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'accepted' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'expired' => 'bg-yellow-100 text-yellow-800',
        ];
        
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getIsExpiredAttribute()
    {
        return $this->valid_until && now()->gt($this->valid_until);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->valid_until) {
            return null;
        }
        
        return now()->diffInDays($this->valid_until, false);
    }
}
