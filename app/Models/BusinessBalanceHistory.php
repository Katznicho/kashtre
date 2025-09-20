<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessBalanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'money_account_id',
        'previous_balance',
        'amount',
        'new_balance',
        'type',
        'description',
        'reference_type',
        'reference_id',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'amount' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the business that owns the balance statement
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the money account associated with this history
     */
    public function moneyAccount()
    {
        return $this->belongsTo(MoneyAccount::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for package transactions
     */
    public function scopePackages($query)
    {
        return $query->where('type', 'package');
    }

    /**
     * Scope for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Record a balance change for a business
     */
    public static function recordChange($businessId, $moneyAccountId, $amount, $type, $description, $referenceType = null, $referenceId = null, $metadata = [], $userId = null)
    {
        $account = MoneyAccount::find($moneyAccountId);
        if (!$account) {
            throw new \Exception("Money account not found");
        }

        $previousBalance = $account->balance;
        $newBalance = $type === 'credit' ? $previousBalance + $amount : $previousBalance - $amount;

        $history = self::create([
            'business_id' => $businessId,
            'money_account_id' => $moneyAccountId,
            'previous_balance' => $previousBalance,
            'amount' => $amount,
            'new_balance' => $newBalance,
            'type' => $type,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);

        // Update the account balance
        $account->update(['balance' => $newBalance]);

        return $history;
    }

    /**
     * Record a package transaction for a business
     * Package transactions don't affect balance - they're just records
     */
    public static function recordPackageTransaction($businessId, $moneyAccountId, $amount, $description, $referenceType = null, $referenceId = null, $metadata = [], $userId = null)
    {
        $account = MoneyAccount::find($moneyAccountId);
        if (!$account) {
            throw new \Exception("Money account not found");
        }

        $previousBalance = $account->balance;
        // Package transactions don't change balance - they're just records
        $newBalance = $previousBalance;

        $history = self::create([
            'business_id' => $businessId,
            'money_account_id' => $moneyAccountId,
            'previous_balance' => $previousBalance,
            'amount' => $amount,
            'new_balance' => $newBalance,
            'type' => 'package',
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);

        // No balance update for package transactions
        // The account balance remains the same

        return $history;
    }
}
