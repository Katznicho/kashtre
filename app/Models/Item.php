<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'group_id',
        'subgroup_id',
        'department_id',
        'uom_id',
        'service_point_id',
        'default_price',
        'hospital_share',
        'contractor_account_id',
        'business_id',
        'other_names',
    ];
    
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function subgroup()
    {
        return $this->belongsTo(Group::class, 'subgroup_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class, 'uom_id');
    }

    public function servicePoint()
    {
        return $this->belongsTo(ServicePoint::class, 'service_point_id');
    }

    public function contractor()
    {
        return $this->belongsTo(ContractorProfile::class, 'contractor_account_id');
    }

    public function branchPrices()
    {
        return $this->hasMany(BranchItemPrice::class);
    }
}
