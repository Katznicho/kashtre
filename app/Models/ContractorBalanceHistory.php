<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorBalanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contractor_profile_id',
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
     * Get the contractor profile that owns the balance history
     */
    public function contractorProfile()
    {
        return $this->belongsTo(ContractorProfile::class);
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
     * Scope for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Record a balance change for a contractor
     */
    public static function recordChange($contractorProfileId, $moneyAccountId, $amount, $type, $description, $referenceType = null, $referenceId = null, $metadata = [], $userId = null)
    {
        $account = MoneyAccount::find($moneyAccountId);
        if (!$account) {
            throw new \Exception("Money account not found");
        }

        $previousBalance = $account->balance;
        $newBalance = $type === 'credit' ? $previousBalance + $amount : $previousBalance - $amount;

        $history = self::create([
            'contractor_profile_id' => $contractorProfileId,
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
}
