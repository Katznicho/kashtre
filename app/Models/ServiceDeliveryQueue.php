<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceDeliveryQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'branch_id',
        'service_point_id',
        'invoice_id',
        'client_id',
        'item_id',
        'item_name',
        'quantity',
        'price',
        'status',
        'priority',
        'notes',
        'queued_at',
        'estimated_delivery_time',
        'started_at',
        'partially_done_at',
        'completed_at',
        'assigned_to',
        'started_by_user_id',
        'is_money_moved',
        'money_moved_at',
        'money_moved_by_user_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'queued_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'started_at' => 'datetime',
        'partially_done_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_money_moved' => 'boolean',
        'money_moved_at' => 'datetime',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function servicePoint()
    {
        return $this->belongsTo(ServicePoint::class);
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function startedByUser()
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartiallyDone($query)
    {
        return $query->where('status', 'partially_done');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForServicePoint($query, $servicePointId)
    {
        return $query->where('service_point_id', $servicePointId);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeMoneyMoved($query)
    {
        return $query->where('is_money_moved', true);
    }

    public function scopeMoneyNotMoved($query)
    {
        return $query->where('is_money_moved', false);
    }



    // Methods
    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function markAsPartiallyDone($userId = null)
    {
        $this->update([
            'status' => 'partially_done',
            'started_at' => $this->started_at ?? now(),
            'partially_done_at' => now(),
            'started_by_user_id' => $userId ?? auth()->id()
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }

    public function assignTo($userId)
    {
        $this->update(['assigned_to' => $userId]);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPartiallyDone()
    {
        return $this->status === 'partially_done';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }



    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'partially_done' => 'bg-orange-100 text-orange-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function isMoneyMoved()
    {
        return $this->is_money_moved === true;
    }

    protected static function booted()
    {
        static::creating(function ($queue) {
            $queue->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Calculate waiting time in seconds
     */
    public function getWaitingTimeInSeconds()
    {
        if ($this->status === 'completed' && $this->completed_at) {
            // For completed items, calculate from queued_at to completed_at
            return $this->queued_at->diffInSeconds($this->completed_at);
        } elseif ($this->status === 'in_progress' && $this->started_at) {
            // For in-progress items, calculate from queued_at to started_at
            return $this->queued_at->diffInSeconds($this->started_at);
        } else {
            // For pending items, calculate from queued_at to now
            return $this->queued_at->diffInSeconds(now());
        }
    }

    /**
     * Format waiting time as 2m:45s
     */
    public function getFormattedWaitingTime()
    {
        $seconds = $this->getWaitingTimeInSeconds();
        return $this->formatSecondsToMinutesSeconds($seconds);
    }

    /**
     * Format seconds to 2m:45s format
     */
    public static function formatSecondsToMinutesSeconds($seconds)
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%dm:%02ds', $minutes, $remainingSeconds);
    }

    /**
     * Get waiting time for display (with real-time updates for pending items)
     */
    public function getWaitingTimeForDisplay()
    {
        if ($this->status === 'pending') {
            // For pending items, return a data attribute for JavaScript to update
            return [
                'formatted' => $this->getFormattedWaitingTime(),
                'seconds' => $this->getWaitingTimeInSeconds(),
                'is_live' => true
            ];
        } else {
            // For completed/in-progress items, return static time
            return [
                'formatted' => $this->getFormattedWaitingTime(),
                'seconds' => $this->getWaitingTimeInSeconds(),
                'is_live' => false
            ];
        }
    }
}
