<?php

namespace App\Services;

use App\Models\BankSchedule;
use App\Models\Business;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\ContractorBalanceHistory;
use App\Models\WithdrawalRequest;
use App\Models\ContractorWithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankScheduleProcessingService
{
    /**
     * Process multiple bank schedules (mark as done)
     */
    public function processBankSchedules(array $bankScheduleIds)
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($bankScheduleIds as $bankScheduleId) {
            try {
                $bankSchedule = BankSchedule::find($bankScheduleId);
                if (!$bankSchedule) {
                    throw new \Exception("Bank schedule not found");
                }
                $this->processBankSchedule($bankSchedule);
                $results['success'][] = $bankScheduleId;
            } catch (\Exception $e) {
                Log::error("Failed to process bank schedule", [
                    'bank_schedule_id' => $bankScheduleId,
                    'error' => $e->getMessage()
                ]);
                $results['failed'][] = [
                    'id' => $bankScheduleId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single bank schedule
     */
    public function processBankSchedule(BankSchedule $bankSchedule)
    {
        DB::beginTransaction();

        try {
            // Check if already processed
            if ($bankSchedule->status === 'processed') {
                Log::warning("Bank schedule already processed", [
                    'bank_schedule_id' => $bankSchedule->id
                ]);
                DB::rollBack();
                return false;
            }

            // Get business
            $business = Business::find($bankSchedule->business_id);
            if (!$business) {
                throw new \Exception("Business not found for bank schedule");
            }

            // Calculate amounts
            $amount = $bankSchedule->amount;
            $charge = $bankSchedule->withdrawal_charge ?? 0;
            $totalDebit = $amount + $charge; // Total to debit from business/contractor

            Log::info("=== BANK SCHEDULE PROCESSING STARTED ===", [
                'bank_schedule_id' => $bankSchedule->id,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'amount' => $amount,
                'charge' => $charge,
                'total_debit' => $totalDebit
            ]);

            // Determine if this is a business or contractor withdrawal
            $withdrawalRequest = null;
            $contractorProfile = null;
            $isContractorWithdrawal = false;

            if ($bankSchedule->withdrawal_request_id) {
                // Check if it's a business withdrawal request
                $withdrawalRequest = WithdrawalRequest::find($bankSchedule->withdrawal_request_id);
                
                // If not found, check contractor withdrawal requests
                if (!$withdrawalRequest) {
                    // Check contractor withdrawal requests (if they also create bank schedules)
                    // For now, assume all bank schedules are from business withdrawals
                    // This can be extended later if contractor withdrawals also create bank schedules
                }
            }

            // Get or create Kashtre account for charge
            $kashtreAccount = $this->getOrCreateKashtreAccount();
            
            // Process based on withdrawal type
            if ($isContractorWithdrawal && $contractorProfile) {
                // Process contractor withdrawal
                $this->processContractorWithdrawal($bankSchedule, $contractorProfile, $amount, $charge, $totalDebit, $kashtreAccount);
            } else {
                // Process business withdrawal (default)
                $this->processBusinessWithdrawal($bankSchedule, $business, $amount, $charge, $totalDebit, $kashtreAccount, $withdrawalRequest);
            }

            // Update bank schedule status
            $bankSchedule->update([
                'status' => 'processed',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Update withdrawal request status if it exists
            if ($withdrawalRequest) {
                $withdrawalRequest->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'processed_by' => auth()->id() ?? 1,
                ]);
            }

            DB::commit();

            Log::info("=== BANK SCHEDULE PROCESSING COMPLETED ===", [
                'bank_schedule_id' => $bankSchedule->id,
                'business_id' => $business->id,
                'total_debit' => $totalDebit,
                'charge_credited_to_kashtre' => $charge
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("=== BANK SCHEDULE PROCESSING FAILED ===", [
                'bank_schedule_id' => $bankSchedule->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process business withdrawal
     */
    private function processBusinessWithdrawal(BankSchedule $bankSchedule, Business $business, $amount, $charge, $totalDebit, MoneyAccount $kashtreAccount, $withdrawalRequest = null)
    {
        // Check if business has sufficient balance
        if ($business->account_balance < $totalDebit) {
            throw new \Exception("Insufficient business account balance for bank schedule processing");
        }

        // Get or create business account
        $businessAccount = $this->getOrCreateBusinessAccount($business);

        // Credit charge to Kashtre account first (if any)
        if ($charge > 0) {
            // Record Kashtre balance statement for charge (this will update the account balance)
            BusinessBalanceHistory::recordChange(
                1, // Kashtre business_id
                $kashtreAccount->id,
                $charge,
                'credit',
                "Bank Schedule Charge - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
                'bank_schedule',
                $bankSchedule->id,
                [
                    'bank_schedule_id' => $bankSchedule->id,
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'withdrawal_amount' => $amount,
                    'charge_amount' => $charge,
                    'description' => "Bank schedule processing charge from {$business->name}"
                ],
                auth()->id()
            );
        }

        // Record business balance statement for withdrawal (amount + charge)
        // This will update the business account balance automatically
        BusinessBalanceHistory::recordChange(
            $business->id,
            $businessAccount->id,
            $totalDebit,
            'debit',
            "Bank Schedule Processed - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
            'bank_schedule',
            $bankSchedule->id,
            [
                'bank_schedule_id' => $bankSchedule->id,
                'withdrawal_amount' => $amount,
                'withdrawal_charge' => $charge,
                'total_debit' => $totalDebit,
                'client_name' => $bankSchedule->client_name,
                'bank_name' => $bankSchedule->bank_name,
                'bank_account' => $bankSchedule->bank_account,
                'description' => "Bank schedule processed: " . ($bankSchedule->reference_id ?? $bankSchedule->id) . " (Amount + Charge)"
            ],
            auth()->id()
        );

        // Update business account balance (sync with business model)
        $business->decrement('account_balance', $totalDebit);

        // Sync with business money account if it exists
        if ($business->businessMoneyAccount) {
            $business->businessMoneyAccount->refresh();
            // Don't call debit() here as recordChange already updated the balance
        }
    }

    /**
     * Process contractor withdrawal (for future extension)
     */
    private function processContractorWithdrawal(BankSchedule $bankSchedule, $contractorProfile, $amount, $charge, $totalDebit, MoneyAccount $kashtreAccount)
    {
        // Check if contractor has sufficient balance
        if ($contractorProfile->account_balance < $totalDebit) {
            throw new \Exception("Insufficient contractor account balance for bank schedule processing");
        }

        // Get or create contractor account
        $contractorAccount = $this->getOrCreateContractorAccount($contractorProfile);

        // Credit charge to Kashtre account first (if any)
        if ($charge > 0) {
            // Record Kashtre balance statement for charge (this will update the account balance)
            BusinessBalanceHistory::recordChange(
                1, // Kashtre business_id
                $kashtreAccount->id,
                $charge,
                'credit',
                "Bank Schedule Charge (Contractor) - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
                'bank_schedule',
                $bankSchedule->id,
                [
                    'bank_schedule_id' => $bankSchedule->id,
                    'contractor_profile_id' => $contractorProfile->id,
                    'withdrawal_amount' => $amount,
                    'charge_amount' => $charge,
                    'description' => "Bank schedule processing charge from contractor"
                ],
                auth()->id()
            );
        }

        // Record contractor balance statement for withdrawal (amount + charge)
        // This will update the contractor account balance automatically
        ContractorBalanceHistory::recordChange(
            $contractorProfile->id,
            $contractorAccount->id,
            $totalDebit,
            'debit',
            "Bank Schedule Processed - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
            'bank_schedule',
            $bankSchedule->id,
            [
                'bank_schedule_id' => $bankSchedule->id,
                'withdrawal_amount' => $amount,
                'withdrawal_charge' => $charge,
                'total_debit' => $totalDebit,
                'client_name' => $bankSchedule->client_name,
                'bank_name' => $bankSchedule->bank_name,
                'bank_account' => $bankSchedule->bank_account,
                'description' => "Bank schedule processed: " . ($bankSchedule->reference_id ?? $bankSchedule->id) . " (Amount + Charge)"
            ],
            auth()->id()
        );

        // Update contractor account balance (sync with contractor profile model)
        $contractorProfile->decrement('account_balance', $totalDebit);

        // Sync with contractor money account if it exists
        if ($contractorProfile->moneyAccount) {
            $contractorProfile->moneyAccount->refresh();
            // Don't call debit() here as recordChange already updated the balance
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
     * Get or create contractor account (for future extension)
     */
    private function getOrCreateContractorAccount($contractorProfile)
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
