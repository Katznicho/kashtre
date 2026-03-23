<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'currency_id',
        'currency_code',
        'exchange_rate_to_usd',
    ];

    protected $casts = [
        'exchange_rate_to_usd' => 'decimal:6',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}

