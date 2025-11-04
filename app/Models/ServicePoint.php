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

    public function supervisor()
    {
        return $this->hasOne(ServicePointSupervisor::class);
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
        // Get ServiceQueue statistics - count unique clients
        $serviceQueuePending = $this->pendingQueues()->distinct('client_id')->count('client_id');
        $serviceQueueInProgress = $this->inProgressQueues()->distinct('client_id')->count('client_id');
        $serviceQueueCompletedToday = $this->completedQueuesToday()->distinct('client_id')->count('client_id');
        $serviceQueueTotalToday = $this->serviceQueues()->today()->distinct('client_id')->count('client_id');
        
        // Get ServiceDeliveryQueue statistics - count unique clients
        $deliveryQueuePending = $this->pendingDeliveryQueues()->distinct('client_id')->count('client_id');
        $deliveryQueuePartiallyDone = $this->partiallyDoneDeliveryQueues()->distinct('client_id')->count('client_id');
        $deliveryQueueInProgress = $this->serviceDeliveryQueues()->where('status', 'in_progress')->distinct('client_id')->count('client_id');
        $deliveryQueueCompletedToday = $this->serviceDeliveryQueues()->where('status', 'completed')->whereDate('completed_at', today())->distinct('client_id')->count('client_id');
        $deliveryQueueTotalToday = $this->serviceDeliveryQueues()->whereDate('queued_at', today())->distinct('client_id')->count('client_id');
        
        // Combine both types of queues - now counting unique clients
        return [
            'pending' => $serviceQueuePending + $deliveryQueuePending,
            'partially_done' => $deliveryQueuePartiallyDone,
            'in_progress' => $serviceQueueInProgress + $deliveryQueueInProgress,
            'completed_today' => $serviceQueueCompletedToday + $deliveryQueueCompletedToday,
            'total_today' => $serviceQueueTotalToday + $deliveryQueueTotalToday,
        ];
    }
}
