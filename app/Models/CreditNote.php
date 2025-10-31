<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'service_delivery_queue_id',
        'invoice_id',
        'client_id',
        'business_id',
        'branch_id',
        'credit_note_number',
        'amount',
        'reason',
        'status',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($creditNote) {
            $creditNote->uuid = (string) Str::uuid();
            
            // Generate credit note number if not provided
            if (!$creditNote->credit_note_number) {
                $creditNote->credit_note_number = 'CN' . date('Y') . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relationships
    public function serviceDeliveryQueue()
    {
        return $this->belongsTo(ServiceDeliveryQueue::class);
    }

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

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
