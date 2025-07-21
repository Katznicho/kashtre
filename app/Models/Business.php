<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'percentage_charge',
        'minimum_amount',
        'type',
        'account_number',
        'account_balance',
        'mode',
        'date'
    ];

    // a businness has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    //a business has many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    //a business has many payment links
    public function paymentLinks()
    {
        return $this->hasMany(PaymentLink::class);
    }

    protected static function booted()
    {
        static::creating(function ($business) {
            $business->uuid = (string) Str::uuid();
        });

        // Clear any cached instances when creating a new business
        static::created(function ($business) {
            // Force refresh the model
            $business->refresh();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public static function getByEmailAndPhone($email, $phone)
    {
        return static::where('email', $email)
                    ->where('phone', $phone)
                    ->orderBy('created_at', 'desc')
                    ->first()
                    ->fresh();  // This ensures we get fresh data from the database
    }
}
