<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'business_id',
        'branch_id',
        'invoice_id',
        'user_id',
        'previous_balance',
        'change_amount',
        'new_balance',
        'transaction_type',
        'description',
        'reference_number',
        'notes',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByTransactionType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isCredit()
    {
        return $this->change_amount > 0;
    }

    public function isDebit()
    {
        return $this->change_amount < 0;
    }

    public function getFormattedChangeAmount()
    {
        $amount = abs($this->change_amount);
        $prefix = $this->isCredit() ? '+' : '-';
        return $prefix . 'UGX ' . number_format($amount, 2);
    }

    public function getFormattedBalance()
    {
        return 'UGX ' . number_format($this->new_balance, 2);
    }

    // Static methods for creating balance statement records
    public static function recordBalanceChange($data)
    {
        return self::create($data);
    }

    public static function recordPayment($client, $invoice, $amount, $paymentMethod = null, $paymentReference = null)
    {
        $previousBalance = $client->balance ?? 0;
        $newBalance = $previousBalance - $amount; // Debit from balance

        return self::create([
            'client_id' => $client->id,
            'business_id' => $client->business_id,
            'branch_id' => $client->branch_id,
            'invoice_id' => $invoice ? $invoice->id : null,
            'user_id' => auth()->id(),
            'previous_balance' => $previousBalance,
            'change_amount' => -$amount, // Negative for debit
            'new_balance' => $newBalance,
            'transaction_type' => 'payment',
            'description' => $invoice ? "Invoice #{$invoice->invoice_number}" : "Balance adjustment",
            'reference_number' => $invoice ? $invoice->invoice_number : null,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'notes' => "Balance used for invoice payment",
        ]);
    }

    public static function recordCredit($client, $amount, $description, $referenceNumber = null, $notes = null, $paymentMethod = null)
    {
        // Calculate previous balance from existing balance history records
        $previousBalance = self::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->value('new_balance') ?? 0;
        
        $newBalance = $previousBalance + $amount; // Credit to balance

        return self::create([
            'client_id' => $client->id,
            'business_id' => $client->business_id,
            'branch_id' => $client->branch_id,
            'user_id' => auth()->id() ?? 1, // Default to user ID 1 if no auth
            'previous_balance' => $previousBalance,
            'change_amount' => $amount, // Positive for credit
            'new_balance' => $newBalance,
            'transaction_type' => 'credit',
            'description' => $description,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
            'payment_method' => $paymentMethod,
        ]);
    }

    public static function recordDebit($client, $amount, $description, $referenceNumber = null, $notes = null, $paymentMethod = null)
    {
        // Calculate previous balance from existing balance history records
        $previousBalance = self::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->value('new_balance') ?? 0;
        
        $newBalance = $previousBalance - $amount; // Debit from balance

        return self::create([
            'client_id' => $client->id,
            'business_id' => $client->business_id,
            'branch_id' => $client->branch_id,
            'user_id' => auth()->id() ?? 1, // Default to user ID 1 if no auth
            'previous_balance' => $previousBalance,
            'change_amount' => -$amount, // Negative for debit
            'new_balance' => $newBalance,
            'transaction_type' => 'debit',
            'description' => $description,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
            'payment_method' => $paymentMethod,
        ]);
    }

    public static function recordAdjustment($client, $amount, $description, $referenceNumber = null, $notes = null)
    {
        $previousBalance = $client->balance ?? 0;
        $newBalance = $previousBalance + $amount; // Can be positive or negative

        return self::create([
            'client_id' => $client->id,
            'business_id' => $client->business_id,
            'branch_id' => $client->branch_id,
            'user_id' => auth()->id(),
            'previous_balance' => $previousBalance,
            'change_amount' => $amount,
            'new_balance' => $newBalance,
            'transaction_type' => 'adjustment',
            'description' => $description,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);
    }

    public static function recordPackageUsage($client, $amount, $description, $referenceNumber = null, $notes = null, $paymentMethod = null)
    {
        // Calculate previous balance from existing balance history records
        $previousBalance = self::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->value('new_balance') ?? 0;
        
        // Package usage doesn't affect balance - it's just a record
        // The balance remains the same since package was already paid for
        $newBalance = $previousBalance;

        return self::create([
            'client_id' => $client->id,
            'business_id' => $client->business_id,
            'branch_id' => $client->branch_id,
            'user_id' => auth()->id() ?? 1, // Default to user ID 1 if no auth
            'previous_balance' => $previousBalance,
            'change_amount' => 0, // No balance change for package usage
            'new_balance' => $newBalance,
            'transaction_type' => 'package',
            'description' => $description,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
            'payment_method' => $paymentMethod,
        ]);
    }
}
