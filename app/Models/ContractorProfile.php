<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'business_id',
        'bank_name',
        'account_name',
        'account_number',
        'account_balance',
        'kashtre_account_number',
        'signing_qualifications',
    ];

    protected $casts = [
        'account_balance' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
