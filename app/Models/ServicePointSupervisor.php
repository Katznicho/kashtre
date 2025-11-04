<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePointSupervisor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_point_id',
        'supervisor_user_id',
        'business_id',
    ];

    // Relationships
    public function servicePoint()
    {
        return $this->belongsTo(ServicePoint::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
