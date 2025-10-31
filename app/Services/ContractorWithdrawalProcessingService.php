<?php

namespace App\Services;

use App\Models\ContractorWithdrawalRequest;
use App\Models\ContractorProfile;
use App\Models\Business;
use App\Models\MoneyAccount;
use App\Models\ContractorBalanceHistory;
use App\Models\MoneyTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractorWithdrawalProcessingService
{
    /**
     * Process approved contractor withdrawal request and deduct money from contractor account
     */
    public function processWithdrawal(ContractorWithdrawalRequest $withdrawalRequest)
    {
        try {
            DB::beginTransaction();

            Log::info("=== CONTRACTOR WITHDRAWAL PROCESSING STARTED ===", [
                'contractor_withdrawal_request_id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'contractor_profile_id' => $withdrawalRequest->contractor_profile_id,
                'business_id' => $withdrawalRequest->business_id,
                'amount' => $withdrawalRequest->amount,
                'withdrawal_charge' => $withdrawalRequest->withdrawal_charge,
                'net_amount' => $withdrawalRequest->net_amount,
                'status' => $withdrawalRequest->status
            ]);

            // Check if withdrawal is already processed
            if ($withdrawalRequest->status === 'completed') {
                Log::warning("Contractor withdrawal already processed", [
                    'contractor_withdrawal_request_id' => $withdrawalRequest->id,
                    'status' => $withdrawalRequest->status
                ]);
                return false;
            }

            // Get contractor profile
            $contractorProfile = ContractorProfile::find($withdrawalRequest->contractor_profile_id);
            if (!$contractorProfile) {
                throw new \Exception("Contractor profile not found for withdrawal request");
            }

            // Get business
            $business = Business::find($withdrawalRequest->business_id);
            if (!$business) {
                throw new \Exception("Business not found for withdrawal request");
            }

            // Calculate amounts
            $serviceFee = $withdrawalRequest->withdrawal_charge;
            $netAmount = $withdrawalRequest->net_amount;
            $totalDebit = $withdrawalRequest->amount + $serviceFee; // amount + charge

            // Check if contractor has sufficient balance (amount + charge)
            if ($contractorProfile->account_balance < $totalDebit) {
                throw new \Exception("Insufficient contractor account balance for withdrawal");
            }

            // Get contractor money account
            $contractorAccount = $this->getOrCreateContractorAccount($contractorProfile);
            
            // Previously: Kashtre account for service fee. Requirement change: no fees should post to Kashtre.

            Log::info("Contractor withdrawal processing details", [
                'contractor_profile_id' => $contractorProfile->id,
                'contractor_name' => $contractorProfile->user->name ?? 'Unknown',
                'contractor_account_balance_before' => $contractorProfile->account_balance,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'withdrawal_amount' => $withdrawalRequest->amount,
                'service_fee' => $serviceFee,
                'net_amount' => $netAmount
            ]);

            // Do not create any MoneyTransfer to business; contractor withdrawals should not touch business accounts

            // Do not transfer or post the service fee anywhere per requirement change.

            // Deduct total (amount + charge) from contractor account per requirement
            $contractorAccount->debit($totalDebit);
            
            // Update contractor account balance
            $contractorProfile->decrement('account_balance', $totalDebit);

            // Sync with contractor money account if it exists
            if ($contractorProfile->moneyAccount) {
                $contractorProfile->moneyAccount->debit($totalDebit);
            }

            // Record contractor balance statement for withdrawal (amount + charge)
            ContractorBalanceHistory::recordChange(
                $contractorProfile->id,
                $contractorAccount->id,
                $totalDebit,
                'debit',
                "Contractor Withdrawal - {$withdrawalRequest->uuid}",
                'contractor_withdrawal',
                $withdrawalRequest->id,
                [
                    'contractor_uuid' => $withdrawalRequest->uuid,
                    'withdrawal_amount' => $withdrawalRequest->amount,
                    'withdrawal_charge' => $serviceFee,
                    'payment_method' => $withdrawalRequest->payment_method,
                    'account_number' => $withdrawalRequest->account_number,
                    'account_name' => $withdrawalRequest->account_name,
                    'bank_name' => $withdrawalRequest->bank_name,
                    'mobile_money_number' => $withdrawalRequest->mobile_money_number,
                    'description' => "Contractor withdrawal processed: {$withdrawalRequest->uuid} (Amount + Charge)"
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

            Log::info("=== CONTRACTOR WITHDRAWAL PROCESSING COMPLETED ===", [
                'contractor_withdrawal_request_id' => $withdrawalRequest->id,
                'uuid' => $withdrawalRequest->uuid,
                'contractor_profile_id' => $contractorProfile->id,
                'contractor_name' => $contractorProfile->user->name ?? 'Unknown',
                'contractor_account_balance_after' => $contractorProfile->fresh()->account_balance,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'net_amount_deducted' => $netAmount,
                'service_fee_collected' => $serviceFee,
                'status' => 'completed'
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("=== CONTRACTOR WITHDRAWAL PROCESSING FAILED ===", [
                'contractor_withdrawal_request_id' => $withdrawalRequest->id,
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
     * Get or create contractor account
     */
    private function getOrCreateContractorAccount(ContractorProfile $contractorProfile)
    {
        $account = MoneyAccount::where('business_id', $contractorProfile->business_id)
            ->where('type', 'contractor_account')
            ->where('contractor_profile_id', $contractorProfile->id)
            ->first();

        if (!$account) {
            $account = MoneyAccount::create([
                'business_id' => $contractorProfile->business_id,
                'type' => 'contractor_account',
                'contractor_profile_id' => $contractorProfile->id,
                'name' => ($contractorProfile->user->name ?? 'Contractor') . ' Account',
                'balance' => 0,
                'currency' => 'UGX'
            ]);
        }

        return $account;
    }

    /**
     * Get or create business account
     */
    private function getOrCreateBusinessAccount($business)
    {
        $account = MoneyAccount::where('business_id', $business->id)
            ->where('type', 'business_account')
            ->first();

        if (!$account) {
            $account = MoneyAccount::create([
                'business_id' => $business->id,
                'type' => 'business_account',
                'name' => $business->name . ' Account',
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
