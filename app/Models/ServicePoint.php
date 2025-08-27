<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'business_id',
        'branch_id',
    ];

    protected $casts = [
        'uuid' => 'string',
        'business_id' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    protected static function booted()
    {
        static::creating(function ($servicePoint) {
            $servicePoint->uuid = (string) Str::uuid();
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceQueues()
    {
        return $this->hasMany(ServiceQueue::class);
    }

    public function serviceDeliveryQueues()
    {
        return $this->hasMany(ServiceDeliveryQueue::class);
    }

    public function pendingDeliveryQueues()
    {
        return $this->serviceDeliveryQueues()->where('status', 'pending')->orderBy('queued_at');
    }

    public function partiallyDoneDeliveryQueues()
    {
        return $this->serviceDeliveryQueues()->where('status', 'partially_done')->orderBy('started_at');
    }

    public function pendingQueues()
    {
        return $this->serviceQueues()->pending()->orderBy('queue_number');
    }

    public function inProgressQueues()
    {
        return $this->serviceQueues()->inProgress()->orderBy('started_at');
    }

    public function completedQueuesToday()
    {
        return $this->serviceQueues()->completed()->today()->orderBy('completed_at', 'desc');
    }

    /**
     * Get the next queue number for this service point
     */
    public function getNextQueueNumberAttribute()
    {
        return ServiceQueue::generateQueueNumber($this->id, $this->business_id);
    }

    /**
     * Get the current queue statistics
     */
    public function getQueueStatsAttribute()
    {
        // Get ServiceQueue statistics
        $serviceQueuePending = $this->pendingQueues()->count();
        $serviceQueueInProgress = $this->inProgressQueues()->count();
        $serviceQueueCompletedToday = $this->completedQueuesToday()->count();
        $serviceQueueTotalToday = $this->serviceQueues()->today()->count();
        
        // Get ServiceDeliveryQueue statistics
        $deliveryQueuePending = $this->pendingDeliveryQueues()->count();
        $deliveryQueuePartiallyDone = $this->partiallyDoneDeliveryQueues()->count();
        $deliveryQueueInProgress = $this->serviceDeliveryQueues()->where('status', 'in_progress')->count();
        $deliveryQueueCompletedToday = $this->serviceDeliveryQueues()->where('status', 'completed')->whereDate('completed_at', today())->count();
        $deliveryQueueTotalToday = $this->serviceDeliveryQueues()->whereDate('queued_at', today())->count();
        
        // Combine both types of queues
        return [
            'pending' => $serviceQueuePending + $deliveryQueuePending,
            'partially_done' => $deliveryQueuePartiallyDone,
            'in_progress' => $serviceQueueInProgress + $deliveryQueueInProgress,
            'completed_today' => $serviceQueueCompletedToday + $deliveryQueueCompletedToday,
            'total_today' => $serviceQueueTotalToday + $deliveryQueueTotalToday,
        ];
    }
}
