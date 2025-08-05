<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'package_item_id',
        'included_item_id',
        'max_quantity',
        'business_id',
    ];

    protected $casts = [
        'max_quantity' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($packageItem) {
            $packageItem->uuid = (string) Str::uuid();
        });
    }

    public function packageItem()
    {
        return $this->belongsTo(Item::class, 'package_item_id');
    }

    public function includedItem()
    {
        return $this->belongsTo(Item::class, 'included_item_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
