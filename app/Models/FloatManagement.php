<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FloatManagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'amount',
        'status',
        'currency',
        'date',
        'channel',
        'proof',
        'date'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function business()
    {
        return $this->belongsTo(Business::class);
    }


    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }


}
