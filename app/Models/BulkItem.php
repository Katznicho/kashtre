<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'bulk_item_id',
        'included_item_id',
        'fixed_quantity',
        'business_id',
    ];

    protected $casts = [
        'fixed_quantity' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($bulkItem) {
            $bulkItem->uuid = (string) Str::uuid();
        });
    }

    public function bulkItem()
    {
        return $this->belongsTo(Item::class, 'bulk_item_id');
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
