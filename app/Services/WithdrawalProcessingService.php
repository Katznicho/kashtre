<?php

namespace App\Services;

use App\Models\WithdrawalRequest;
use App\Models\Business;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\MoneyTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalProcessingService
{
    /**
     * Process approved withdrawal request and deduct money from business account
     */
    public function processWithdrawal(WithdrawalRequest $withdrawalRequest)
    {
        try {
            DB::beginTransaction();

            Log::info("=== WITHDRAWAL PROCESSING STARTED ===", [
                'withdrawal_request_id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'business_id' => $withdrawalRequest->business_id,
                'amount' => $withdrawalRequest->amount,
                'withdrawal_charge' => $withdrawalRequest->withdrawal_charge,
                'net_amount' => $withdrawalRequest->net_amount,
                'status' => $withdrawalRequest->status
            ]);

            // Check if withdrawal is already processed
            if ($withdrawalRequest->status === 'completed') {
                Log::warning("Withdrawal already processed", [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'status' => $withdrawalRequest->status
                ]);
                return false;
            }

            // Get business
            $business = Business::find($withdrawalRequest->business_id);
            if (!$business) {
                throw new \Exception("Business not found for withdrawal request");
            }

            // Calculate amounts
            $serviceFee = $withdrawalRequest->withdrawal_charge;
            $netAmount = $withdrawalRequest->net_amount; // equals requested amount if aligned to UI payout
            $totalDebit = $withdrawalRequest->amount + $serviceFee; // amount + charge (what we deduct)

            // Check if business has sufficient balance (amount + charge)
            if ($business->account_balance < $totalDebit) {
                throw new \Exception("Insufficient business account balance for withdrawal");
            }

            // Get business money account
            $businessAccount = $this->getOrCreateBusinessAccount($business);
            
            // Do not use Kashtre account for fees; fees are not posted to Kashtre per requirements

            Log::info("Withdrawal processing details", [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_account_balance_before' => $business->account_balance,
                'withdrawal_amount' => $withdrawalRequest->amount,
                'service_fee' => $serviceFee,
                'net_amount' => $netAmount
            ]);

            // Do not create MoneyTransfer records; payout happens off-system

            // Service fee: do NOT credit Kashtre or post to its statement.

            // Deduct total (amount + charge) from business account
            $businessAccount->debit($totalDebit);
            
            // Update business account balance
            $business->decrement('account_balance', $totalDebit);

            // Sync with business money account if it exists
            if ($business->businessMoneyAccount) {
                $business->businessMoneyAccount->debit($totalDebit);
            }

            // Record business balance statement for withdrawal (amount + charge)
            BusinessBalanceHistory::recordChange(
                $business->id,
                $businessAccount->id,
                $totalDebit,
                'debit',
                "Withdrawal - {$withdrawalRequest->uuid}",
                'withdrawal',
                $withdrawalRequest->id,
                [
                    'withdrawal_uuid' => $withdrawalRequest->uuid,
                    'withdrawal_amount' => $withdrawalRequest->amount,
                    'withdrawal_charge' => $serviceFee,
                    'payment_method' => $withdrawalRequest->payment_method,
                    'account_number' => $withdrawalRequest->account_number,
                    'account_name' => $withdrawalRequest->account_name,
                    'bank_name' => $withdrawalRequest->bank_name,
                    'mobile_money_number' => $withdrawalRequest->mobile_money_number,
                    'description' => "Withdrawal processed: {$withdrawalRequest->uuid} (Amount + Charge)"
                ]
            );

            // Update withdrawal request status
            $withdrawalRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
                'processed_by' => auth()->id() ?? 1, // Use authenticated user or default to admin
                'transaction_reference' => $withdrawalRequest->uuid
            ]);

            DB::commit();

            Log::info("=== WITHDRAWAL PROCESSING COMPLETED ===", [
                'withdrawal_request_id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_account_balance_after' => $business->fresh()->account_balance,
                'total_amount_deducted' => $totalDebit,
                'service_fee_collected' => $serviceFee,
                'status' => 'completed'
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("=== WITHDRAWAL PROCESSING FAILED ===", [
                'withdrawal_request_id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update withdrawal request status to failed
            $withdrawalRequest->update([
                'status' => 'failed',
                'rejection_reason' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get or create business account
     */
    private function getOrCreateBusinessAccount(Business $business)
    {
        $account = MoneyAccount::where('business_id', $business->id)
            ->where('type', 'business_account')
            ->first();

        if (!$account) {
            $account = MoneyAccount::create([
                'business_id' => $business->id,
                'type' => 'business_account',
                'name' => "{$business->name} Business Account",
                'balance' => 0,
                'currency' => 'UGX'
            ]);
        }

        return $account;
    }

    /**
     * Get or create Kashtre account
     */
    private function getOrCreateKashtreAccount()
    {
        $account = MoneyAccount::where('business_id', 1)
            ->where('type', 'kashtre_account')
            ->first();

        if (!$account) {
            $account = MoneyAccount::create([
                'business_id' => 1,
                'type' => 'kashtre_account',
                'name' => 'Kashtre Main Account',
                'balance' => 0,
                'currency' => 'UGX'
            ]);
        }

        return $account;
    }
}

