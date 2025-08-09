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
        'default_price',
        'validity_days',
        'hospital_share',
        'contractor_account_id',
        'business_id',
        'other_names',
    ];
    
    protected static function booted()
    {
        static::creating(function ($item) {
            $item->uuid = (string) Str::uuid();
            
            // Auto-generate code if not provided
            if (empty($item->code)) {
                $item->code = self::generateUniqueCode($item->business_id);
            }
        });
    }

    /**
     * Generate a unique item code for the given business
     */
    public static function generateUniqueCode($businessId)
    {
        $prefix = 'ITM';
        
        // Use microtime to ensure uniqueness even in concurrent imports
        $timestamp = microtime(true);
        $microseconds = ($timestamp - floor($timestamp)) * 1000000;
        $uniqueId = floor($timestamp) . str_pad($microseconds, 6, '0', STR_PAD_LEFT);
        
        // Get the last 6 digits for the code
        $codeNumber = substr($uniqueId, -6);
        
        return $prefix . $codeNumber;
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

    public function contractor()
    {
        return $this->belongsTo(ContractorProfile::class, 'contractor_account_id');
    }

    public function branchPrices()
    {
        return $this->hasMany(BranchItemPrice::class);
    }

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class, 'package_item_id');
    }

    public function bulkItems()
    {
        return $this->hasMany(BulkItem::class, 'bulk_item_id');
    }

    public function includedInPackages()
    {
        return $this->hasMany(PackageItem::class, 'included_item_id');
    }

    public function includedInBulks()
    {
        return $this->hasMany(BulkItem::class, 'included_item_id');
    }

    public function branchServicePoints()
    {
        return $this->hasMany(BranchServicePoint::class, 'item_id');
    }
}
