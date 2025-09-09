<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'business_id',
        'branch_id',
        'client_id',
        'visit_id',
        'name',
        'nin',
        'tin_number',
        'surname',
        'first_name',
        'other_names',
        'sex',
        'date_of_birth',
        'marital_status',
        'occupation',
        'phone_number',
        'village',
        'county',
        'email',
        'services_category',
        'payment_methods',
        'payment_phone_number',
        // Next of Kin details
        'nok_surname',
        'nok_first_name',
        'nok_other_names',
        'nok_sex',
        'nok_marital_status',
        'nok_occupation',
        'nok_phone_number',
        'nok_village',
        'nok_county',
        'balance',
        'status',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'date_of_birth' => 'date',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function balanceHistories()
    {
        return $this->hasMany(BalanceHistory::class);
    }

    /**
     * Get the full name of the client
     */
    public function getFullNameAttribute()
    {
        return trim($this->surname . ' ' . $this->first_name . ' ' . ($this->other_names ?? ''));
    }

    /**
     * Get the full name of the next of kin
     */
    public function getNokFullNameAttribute()
    {
        return trim($this->nok_surname . ' ' . $this->nok_first_name . ' ' . ($this->nok_other_names ?? ''));
    }

    /**
     * Get the client's total balance (calculated from balance history)
     */
    public function getTotalBalanceAttribute()
    {
        // Calculate total balance from balance history records
        $totalBalance = $this->balanceHistories()
            ->selectRaw('SUM(CASE WHEN transaction_type = "credit" THEN change_amount ELSE -change_amount END) as total')
            ->value('total') ?? 0;
        
        return $totalBalance;
    }

    /**
     * Get the client's available balance (money available for transactions)
     * This should be 0 since money is in suspense accounts
     */
    public function getAvailableBalanceAttribute()
    {
        // Available balance should be 0 since money is locked in suspense accounts
        // This represents money that the client can actually use for new transactions
        return 0;
    }

    /**
     * Get the client's suspense balance (money in temporary accounts)
     * This should return the actual balance from the suspense account
     */
    public function getSuspenseBalanceAttribute()
    {
        // Get the actual suspense account balance
        $suspenseAccount = $this->suspenseAccounts()
            ->where('type', 'general_suspense_account')
            ->first();
        
        return $suspenseAccount ? $suspenseAccount->balance : 0;
    }

    /**
     * Get the client's money account
     */
    public function moneyAccount()
    {
        return $this->hasOne(MoneyAccount::class)->where('type', 'client_account');
    }

    /**
     * Get the client's suspense accounts
     */
    public function suspenseAccounts()
    {
        return $this->hasMany(MoneyAccount::class)->whereIn('type', ['general_suspense_account', 'package_suspense_account']);
    }

    /**
     * Generate a unique client ID based on NIN, business, and branch
     * Format: 3 letters + 4 numbers + 1 letter
     */
    public static function generateClientId($nin, $business, $branch)
    {
        // Get 3 letters: first 2 from business name + 1 from branch name
        $businessPrefix = strtoupper(substr($business->name, 0, 2));
        $branchPrefix = strtoupper(substr($branch->name, 0, 1));
        $threeLetters = $businessPrefix . $branchPrefix;
        
        // Get 4 numbers from NIN or generate random
        if ($nin && strlen($nin) >= 4) {
            $fourNumbers = substr($nin, -4);
        } else {
            // Generate 4 random numbers
            $fourNumbers = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        // Get 1 letter at the end
        if ($nin) {
            // Use first letter of NIN if available
            $lastLetter = strtoupper(substr($nin, 0, 1));
        } else {
            // Use random letter for clients without NIN
            $lastLetter = strtoupper(Str::random(1));
        }
        
        // Format: 3 letters + 4 numbers + 1 letter (exactly 8 characters)
        return $threeLetters . $fourNumbers . $lastLetter;
    }

    /**
     * Generate a visit ID based on business and branch
     */
    public static function generateVisitId($business, $branch)
    {
        // Get the first letter of the first two words of business name
        $businessWords = explode(' ', $business->name);
        $businessPrefix = '';
        if (count($businessWords) >= 2) {
            $businessPrefix = strtoupper(substr($businessWords[0], 0, 1) . substr($businessWords[1], 0, 1));
        } else {
            $businessPrefix = strtoupper(substr($business->name, 0, 2));
        }

        // Get the first letter of branch name
        $branchLetter = strtoupper(substr($branch->name, 0, 1));

        // Get today's count for this business and branch
        $todayCount = self::where('business_id', $business->id)
            ->where('branch_id', $branch->id)
            ->whereDate('created_at', today())
            ->count() + 1;

        // Format the count as 2-digit number
        $countStr = str_pad($todayCount, 2, '0', STR_PAD_LEFT);

        // If count exceeds 99, reset to 01 and increment branch letter
        if ($todayCount > 99) {
            $countStr = '01';
            $branchLetter = chr(ord($branchLetter) + 1);
        }

        return $businessPrefix . $countStr . $branchLetter;
    }

    /**
     * Get the age of the client
     */
    public function getAgeAttribute()
    {
        if ($this->date_of_birth) {
            return now()->diffInYears($this->date_of_birth);
        }
        return null;
    }
}
