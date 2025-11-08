<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'visit_expires_at',
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
        'visit_expires_at' => 'datetime',
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

    public function serviceDeliveryQueues()
    {
        return $this->hasMany(ServiceDeliveryQueue::class);
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
     * Get the client's total balance (money in suspense accounts)
     */
    public function getTotalBalanceAttribute()
    {
        return $this->getAvailableBalanceAttribute() + $this->getSuspenseBalanceAttribute();
    }

    /**
     * Get the client's available balance (money available for transactions)
     */
    public function getAvailableBalanceAttribute()
    {
        return MoneyAccount::where('client_id', $this->id)
            ->where('type', 'client_account')
            ->sum('balance');
    }

    /**
     * Get the client's suspense balance (money in suspense accounts that is NOT MOVED yet)
     * This should return the actual balance from ALL suspense accounts that haven't been moved to final accounts
     */
    public function getSuspenseBalanceAttribute()
    {
        // Get the sum of ALL suspense account balances for this client
        // All suspense accounts are now client-specific
        $totalSuspenseBalance = $this->suspenseAccounts()->sum('balance');
        
        return $totalSuspenseBalance;
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
        return $this->hasMany(MoneyAccount::class)->whereIn('type', [
            'general_suspense_account', 
            'package_suspense_account',
            'kashtre_suspense_account'
        ]);
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
        // Build prefix from business and branch context
        $businessWords = preg_split('/\s+/', trim($business->name));
        $businessPrefix = '';

        if (count($businessWords) >= 2) {
            $businessPrefix = strtoupper(substr($businessWords[0], 0, 1) . substr($businessWords[1], 0, 1));
        } else {
            $businessPrefix = strtoupper(substr($business->name, 0, 2));
        }

        $branchLetter = strtoupper(substr($branch->name, 0, 1));

        $dateSegment = now()->format('ymd');

        do {
            $randomSegment = strtoupper(Str::random(3));
            $candidate = sprintf('%s%s-%s-%s', $businessPrefix, $branchLetter, $dateSegment, $randomSegment);
        } while (
            self::withTrashed()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->where('visit_id', $candidate)
                ->exists()
        );

        return $candidate;
    }

    /**
     * Ensure the client has a valid visit ID for the current day.
     */
    public function ensureActiveVisitId(bool $force = false): void
    {
        $needsRefresh = $force
            || empty($this->visit_id)
            || empty($this->visit_expires_at)
            || Carbon::parse($this->visit_expires_at)->isPast();

        if (! $needsRefresh) {
            return;
        }

        $this->issueNewVisitId();
    }

    /**
     * Issue a new visit ID and extend validity to the next midnight.
     */
    public function issueNewVisitId(): void
    {
        $business = $this->business ?: Business::find($this->business_id);
        $branch = $this->branch ?: Branch::find($this->branch_id);

        if (! $business || ! $branch) {
            return;
        }

        $this->visit_id = self::generateVisitId($business, $branch);
        $this->visit_expires_at = Carbon::tomorrow()->startOfDay();
        $this->save();
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
