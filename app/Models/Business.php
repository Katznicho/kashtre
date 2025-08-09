<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function titles()
    {
        return $this->hasMany(Title::class);
    }
}
