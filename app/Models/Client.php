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
     * Generate a business-scoped client ID.
     *
     * Format: <B1><B2><CODE7>
     *  - <B1><B2>: First two letters of the business name (upper-cased; fallbacks ensure two characters).
     *  - <CODE7>:  Deterministic 7-character alphanumeric string derived from surname, first name, DOB.
     *
     * Ensures uniqueness within the business by retrying with a sequence suffix if a collision occurs.
     */
    public static function generateClientId($business, string $surname = '', string $firstName = '', ?string $dateOfBirth = null): string
    {
        if (! $business) {
            throw new \InvalidArgumentException('Business context is required when generating a client ID.');
        }

        $businessPrefix = self::resolveBusinessPrefixForClientId((string) $business->name);
        $normalizedDob = self::normalizeDateForClientId($dateOfBirth);

        $attempt = 0;
        $maxAttempts = 50;

        do {
            $code = self::buildSevenCharacterCode($surname, $firstName, $normalizedDob, $attempt);
            $candidate = $businessPrefix . $code;

            $exists = self::withTrashed()
                ->where('business_id', $business->id)
                ->where('client_id', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }

            $attempt++;
        } while ($attempt < $maxAttempts);

        throw new \RuntimeException('Unable to generate a unique client ID after multiple attempts.');
    }

    protected static function resolveBusinessPrefixForClientId(string $businessName): string
    {
        $businessName = trim($businessName);
        if ($businessName === '') {
            return 'XX';
        }

        $letters = strtoupper(preg_replace('/[^A-Z]/i', '', $businessName));
        $prefix = substr($letters, 0, 2);

        return str_pad($prefix ?: 'XX', 2, 'X');
    }

    protected static function normalizeDateForClientId(?string $dateOfBirth): string
    {
        if (empty($dateOfBirth)) {
            return '00000000';
        }

        try {
            return Carbon::parse($dateOfBirth)->format('Ymd');
        } catch (\Throwable $e) {
            return '00000000';
        }
    }

    protected static function buildSevenCharacterCode(string $surname, string $firstName, string $normalizedDob, int $attempt = 0): string
    {
        $source = strtoupper(trim($surname)) . '|' . strtoupper(trim($firstName)) . '|' . $normalizedDob . '|' . $attempt;
        $hash = strtoupper(base_convert(md5($source), 16, 36));
        $clean = preg_replace('/[^A-Z0-9]/', '', $hash);

        if ($clean === '' || strlen($clean) < 7) {
            $clean = str_pad($clean, 7, 'X');
        }

        return substr($clean, 0, 7);
    }

    /**
     * Generate a visit ID based on the business and branch.
     *
     * Format: <B1><B2><NN><R>
     *  - <B1><B2>: First letters from the first two words of the business name
     *              (fallbacks ensure two alphabetic characters).
     *  - <NN>:     Two-digit sequence unique within a branch (01-99).
     *  - <R>:      First letter of the branch name (fallback to X).
     *
     * Example: KH01M (Kentucky Hospital, Maganjo branch).
     */
    public static function generateVisitId($business, $branch)
    {
        if (! $business || ! $branch) {
            throw new \InvalidArgumentException('Both business and branch are required to generate a visitor ID.');
        }

        $businessPrefix = self::resolveBusinessPrefix($business?->name);
        $branchLetter = self::resolveBranchLetter($branch?->name);

        $existingVisitIds = self::withTrashed()
            ->where('branch_id', $branch->id)
            ->whereNotNull('visit_id')
            ->where(function ($query) {
                $query->whereNull('visit_expires_at')
                    ->orWhere('visit_expires_at', '>', now());
            })
            ->pluck('visit_id');

        $usedSequences = $existingVisitIds
            ->filter(function ($visitId) use ($businessPrefix, $branchLetter) {
                return preg_match(
                    sprintf('/^%s(\d{2})%s$/', preg_quote($businessPrefix, '/'), preg_quote($branchLetter, '/')),
                    $visitId
                );
            })
            ->map(function ($visitId) use ($businessPrefix, $branchLetter) {
                preg_match(
                    sprintf('/^%s(\d{2})%s$/', preg_quote($businessPrefix, '/'), preg_quote($branchLetter, '/')),
                    $visitId,
                    $matches
                );

                return isset($matches[1]) ? (int) $matches[1] : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        for ($sequence = 1; $sequence <= 99; $sequence++) {
            if (! $usedSequences->contains($sequence)) {
                $sequenceSegment = str_pad($sequence, 2, '0', STR_PAD_LEFT);
                return $businessPrefix . $sequenceSegment . $branchLetter;
            }
        }

        throw new \RuntimeException('No available visitor IDs for this branch. Please review visitor ID allocations.');
    }

    /**
     * Resolve the two-letter business prefix for visit IDs.
     */
    protected static function resolveBusinessPrefix(?string $businessName): string
    {
        $businessName = trim((string) $businessName);

        if ($businessName === '') {
            return 'XX';
        }

        $words = array_values(array_filter(preg_split('/\s+/', $businessName)));

        if (count($words) >= 2) {
            $first = strtoupper(substr($words[0], 0, 1));
            $second = strtoupper(substr($words[1], 0, 1));
        } else {
            $firstWord = $words[0];
            $first = strtoupper(substr($firstWord, 0, 1));
            $second = strtoupper(substr($firstWord, 1, 1) ?: $first);
        }

        $first = $first ?: 'X';
        $second = $second ?: 'X';

        return $first . $second;
    }

    /**
     * Resolve the branch letter suffix for visit IDs.
     */
    protected static function resolveBranchLetter(?string $branchName): string
    {
        $branchName = trim((string) $branchName);

        if ($branchName === '') {
            return 'X';
        }

        $letter = strtoupper(substr($branchName, 0, 1));

        return $letter ?: 'X';
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
