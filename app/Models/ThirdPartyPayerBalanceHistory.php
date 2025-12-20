<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyPayerBalanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'third_party_payer_id',
        'business_id',
        'branch_id',
        'invoice_id',
        'client_id',
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
        'payment_status',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function thirdPartyPayer()
    {
        return $this->belongsTo(ThirdPartyPayer::class);
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static methods for creating balance history records
    public static function recordDebit($thirdPartyPayer, $amount, $description, $referenceNumber = null, $notes = null, $paymentMethod = null, $invoiceId = null, $clientId = null)
    {
        // Check if a debit entry already exists for this invoice with the same description to prevent duplicates
        if ($invoiceId) {
            $existingDebit = self::where('third_party_payer_id', $thirdPartyPayer->id)
                ->where('invoice_id', $invoiceId)
                ->where('transaction_type', 'debit')
                ->where('description', $description)
                ->first();
            
            if ($existingDebit) {
                \Log::info("Debit entry already exists for invoice with same description", [
                    'invoice_id' => $invoiceId,
                    'third_party_payer_id' => $thirdPartyPayer->id,
                    'description' => $description,
                    'existing_debit_id' => $existingDebit->id
                ]);
                return $existingDebit;
            }
        }
        
        // Calculate previous balance from existing balance history records
        $previousBalance = self::where('third_party_payer_id', $thirdPartyPayer->id)
            ->orderBy('created_at', 'desc')
            ->value('new_balance') ?? ($thirdPartyPayer->current_balance ?? 0);
        
        $newBalance = $previousBalance - $amount; // Debit from balance

        // Update the third-party payer's current_balance
        $thirdPartyPayer->update(['current_balance' => $newBalance]);

        return self::create([
            'third_party_payer_id' => $thirdPartyPayer->id,
            'business_id' => $thirdPartyPayer->business_id,
            'branch_id' => $thirdPartyPayer->business->branches()->first()?->id ?? null,
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'user_id' => auth()->id() ?? 1,
            'previous_balance' => $previousBalance,
            'change_amount' => -$amount, // Negative for debit
            'new_balance' => $newBalance,
            'transaction_type' => 'debit',
            'description' => $description,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_payment',
        ]);
    }
}
