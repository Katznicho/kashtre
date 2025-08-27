<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
class Transaction extends Model
{
    use HasFactory, SoftDeletes;



     protected $fillable = [
        'business_id',
        'branch_id',
        'amount',
        'reference',
        'description',
        'status',
        'type',
        'origin',
        'phone_number',
        'provider',
        'service',
        'date',
        'currency',
        'names',
        'email',
        'ip_address',
        'user_agent',
        'method',
        'transaction_for'
     ];



     //relationships
     public function business()
     {
         return $this->belongsTo(Business::class);
     }

     public function branch()
     {
         return $this->belongsTo(Branch::class);
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
