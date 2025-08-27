<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
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
        'amount_paid',
        'balance_due',
        'payment_methods',
        'payment_status',
        'notes',
        'status',
        'confirmed_at',
        'printed_at',
    ];

    protected $casts = [
        'items' => 'array',
        'payment_methods' => 'array',
        'subtotal' => 'decimal:2',
        'package_adjustment' => 'decimal:2',
        'account_balance_adjustment' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    // Relationships
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

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
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

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    // Methods
    public static function generateInvoiceNumber($businessId)
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this business and month
        $lastInvoice = self::where('business_id', $businessId)
            ->where('invoice_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function markAsPrinted()
    {
        $this->update([
            'printed_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    public function updatePaymentStatus()
    {
        if ($this->amount_paid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->balance_due = $this->total_amount - $this->amount_paid;
        $this->save();
    }

    // Accessors
    public function getFormattedInvoiceNumberAttribute()
    {
        return $this->invoice_number;
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'UGX ' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceDueAttribute()
    {
        return 'UGX ' . number_format($this->balance_due, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-gray-100 text-gray-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'printed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'partial' => 'bg-orange-100 text-orange-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        
        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }
}
