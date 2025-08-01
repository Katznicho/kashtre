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
        static::creating(function ($contractor) {
            $contractor->uuid = (string) Str::uuid();
            
            // Generate kashtre_account_number if not provided
            if (empty($contractor->kashtre_account_number)) {
                $contractor->kashtre_account_number = self::generateKashtreAccountNumber();
            }
        });
    }

    /**
     * Generate a unique kashtre account number
     * Format: KC + 10 random alphanumeric characters
     */
    public static function generateKashtreAccountNumber()
    {
        do {
            // Generate 10 random alphanumeric characters
            $randomChars = Str::random(10);
            $kashtreAccountNumber = 'KC' . $randomChars;
            
            // Check if this account number already exists
            $exists = self::where('kashtre_account_number', $kashtreAccountNumber)->exists();
        } while ($exists);
        
        return $kashtreAccountNumber;
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
