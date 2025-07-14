<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BalanceHistory extends Model
{
    use HasFactory;



    protected $fillable = [
        'user_id',
        'business_id',
        'balance_before',
        'balance_after',
        'amount',
        'date',
    ];

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
