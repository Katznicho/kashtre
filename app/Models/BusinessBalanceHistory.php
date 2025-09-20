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
        \Log::info("=== CREATING PACKAGE TRANSACTION BUSINESS BALANCE HISTORY RECORD ===", [
            'business_id' => $businessId,
            'money_account_id' => $moneyAccountId,
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString()
        ]);

        $account = MoneyAccount::find($moneyAccountId);
        if (!$account) {
            \Log::error("Money account not found for package transaction", [
                'money_account_id' => $moneyAccountId,
                'business_id' => $businessId
            ]);
            throw new \Exception("Money account not found");
        }

        \Log::info("Money account found for package transaction", [
            'money_account_id' => $moneyAccountId,
            'account_name' => $account->name,
            'account_type' => $account->type,
            'current_balance' => $account->balance
        ]);

        $previousBalance = $account->balance;
        // Package transactions don't change balance - they're just records
        $newBalance = $previousBalance;

        $businessBalanceHistoryData = [
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
        ];

        \Log::info("Creating BusinessBalanceHistory record for package transaction", [
            'business_balance_history_data' => $businessBalanceHistoryData
        ]);

        $history = self::create($businessBalanceHistoryData);

        \Log::info("Package transaction BusinessBalanceHistory record created successfully", [
            'business_balance_history_id' => $history->id,
            'business_id' => $businessId,
            'money_account_id' => $moneyAccountId,
            'type' => 'package',
            'amount' => $amount,
            'description' => $description,
            'note' => 'No balance update for package transactions - they are records only'
        ]);

        // No balance update for package transactions
        // The account balance remains the same

        return $history;
    }
}
