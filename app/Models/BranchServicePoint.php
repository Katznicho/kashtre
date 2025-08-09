<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchServicePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'branch_id',
        'service_point_id',
        'item_id',
    ];

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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
