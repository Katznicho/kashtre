<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'business_id',
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
}
