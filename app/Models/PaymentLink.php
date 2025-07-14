<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use HasFactory;



    protected $fillable = [
        'uuid',
        'user_id',
        'business_id',
        'title',
        'amount',
        'amount_in_words',
        'minimum_amount',
        'is_fixed',
        'currency',
        'expiry_date',
        'reference',
        'redirect_url',
        'description',
        'type',
        'status',
        'website_url',
        'is_customer_info_required',
        'is_active',
        'date',
        'customer_fields',
        'mobile_money_number',
        'card_details',
        'method',
    ];

    protected $casts = [
        'customer_fields' => 'array',
        'expiry_date' => 'datetime',
    ];

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
