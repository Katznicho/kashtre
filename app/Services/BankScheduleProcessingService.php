<?php

namespace App\Services;

use App\Models\BankSchedule;
use App\Models\Business;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\ContractorBalanceHistory;
use App\Models\WithdrawalRequest;
use App\Models\ContractorWithdrawalRequest;
use App\Models\MoneyTransfer;
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
            $totalDebit = $amount + $charge; // Total to debit from business account (will be two separate debit entries)

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
        // Get business account
        $businessAccount = MoneyAccount::where('business_id', $business->id)
            ->where('type', 'business_account')
            ->first();

        if (!$businessAccount) {
            throw new \Exception("Business account not found for business: {$business->name}");
        }

        // Get withdrawal suspense account
        $moneyTrackingService = new \App\Services\MoneyTrackingService();
        $withdrawalSuspenseAccount = $moneyTrackingService->getOrCreateWithdrawalSuspenseAccount($business);

        // Check if withdrawal suspense account has sufficient balance
        if ($withdrawalSuspenseAccount->balance < $totalDebit) {
            throw new \Exception("Insufficient balance in withdrawal suspense account. Available: " . number_format($withdrawalSuspenseAccount->balance, 2) . " UGX, Required: " . number_format($totalDebit, 2) . " UGX");
        }

        $referenceId = $bankSchedule->reference_id ?? $bankSchedule->id;

        // Credit charge to Kashtre account statement (if any)
        if ($charge > 0) {
            // Record Kashtre balance statement for charge (this will update the account balance)
            BusinessBalanceHistory::recordChange(
                1, // Kashtre business_id
                $kashtreAccount->id,
                $charge,
                'credit',
                "Withdrawal Charge - {$referenceId}",
                'bank_schedule',
                $bankSchedule->id,
                [
                    'bank_schedule_id' => $bankSchedule->id,
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'withdrawal_amount' => $amount,
                    'charge_amount' => $charge,
                    'description' => "Withdrawal processing charge from {$business->name}"
                ],
                auth()->id()
            );
        }

        // Debit from withdrawal suspense account (release the hold)
        BusinessBalanceHistory::recordChange(
            $business->id,
            $withdrawalSuspenseAccount->id,
            $totalDebit,
            'debit',
                "Withdrawal Processed - {$referenceId}",
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
                'description' => "Withdrawal processed: {$referenceId} - releasing hold from suspense account"
            ],
            auth()->id()
        );

        // Debit 1: Withdrawal Charge from business account (this is the actual debit entry)
        if ($charge > 0) {
            BusinessBalanceHistory::recordChange(
                $business->id,
                $businessAccount->id,
                $charge,
                'debit',
                "Withdrawal Charge - {$referenceId}",
                'bank_schedule',
                $bankSchedule->id,
                [
                    'bank_schedule_id' => $bankSchedule->id,
                    'withdrawal_charge' => $charge,
                    'client_name' => $bankSchedule->client_name,
                    'bank_name' => $bankSchedule->bank_name,
                    'bank_account' => $bankSchedule->bank_account,
                    'description' => "Withdrawal charge processed: {$referenceId}"
                ],
                auth()->id()
            );
        }

        // Debit 2: Withdrawal Amount from business account (this is the actual debit entry)
        BusinessBalanceHistory::recordChange(
            $business->id,
            $businessAccount->id,
            $amount,
            'debit',
                "Withdrawal - {$referenceId}",
            'bank_schedule',
            $bankSchedule->id,
            [
                'bank_schedule_id' => $bankSchedule->id,
                'withdrawal_amount' => $amount,
                'client_name' => $bankSchedule->client_name,
                'bank_name' => $bankSchedule->bank_name,
                'bank_account' => $bankSchedule->bank_account,
                'description' => "Withdrawal processed: {$referenceId}"
            ],
            auth()->id()
        );

        // Create MoneyTransfer record for suspense account debit
        MoneyTransfer::create([
            'business_id' => $business->id,
            'from_account_id' => $withdrawalSuspenseAccount->id,
            'to_account_id' => null, // Money leaves the system
            'amount' => $totalDebit,
            'currency' => 'UGX',
            'status' => 'completed',
            'type' => 'debit',
            'transfer_type' => 'withdrawal_processed',
                'description' => "Withdrawal Processed - {$referenceId}",
            'source' => $withdrawalSuspenseAccount->name,
            'destination' => "External Payment",
            'processed_at' => now(),
        ]);

        // Create MoneyTransfer record for the withdrawal amount from business account
        MoneyTransfer::create([
            'business_id' => $business->id,
            'from_account_id' => $businessAccount->id,
            'to_account_id' => null, // Money leaves the system
            'amount' => $amount,
            'currency' => 'UGX',
            'status' => 'completed',
            'type' => 'debit',
            'transfer_type' => 'withdrawal_processed',
                'description' => "Withdrawal Processed - {$referenceId}",
            'source' => $businessAccount->name,
            'destination' => "External Payment",
            'processed_at' => now(),
        ]);

        // Create MoneyTransfer record for the charge from business account (if any)
        if ($charge > 0) {
            MoneyTransfer::create([
                'business_id' => $business->id,
                'from_account_id' => $businessAccount->id,
                'to_account_id' => $kashtreAccount->id,
                'amount' => $charge,
                'currency' => 'UGX',
                'status' => 'completed',
                'type' => 'debit',
                'transfer_type' => 'withdrawal_charge',
                'description' => "Withdrawal Charge - {$referenceId}",
                'source' => $businessAccount->name,
                'destination' => $kashtreAccount->name,
                'processed_at' => now(),
            ]);
        }

        Log::info("Bank schedule processed - funds released from suspense and debited from business account", [
            'bank_schedule_id' => $bankSchedule->id,
            'withdrawal_suspense_account_id' => $withdrawalSuspenseAccount->id,
            'business_account_id' => $businessAccount->id,
            'withdrawal_amount' => $amount,
            'withdrawal_charge' => $charge,
            'total_debit' => $totalDebit,
            'charge_credited_to_kashtre' => $charge,
            'remaining_suspense_balance' => $withdrawalSuspenseAccount->fresh()->balance,
            'remaining_business_balance' => $businessAccount->fresh()->balance
        ]);
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
                "Withdrawal Charge (Contractor) - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
                'bank_schedule',
                $bankSchedule->id,
                [
                    'bank_schedule_id' => $bankSchedule->id,
                    'contractor_profile_id' => $contractorProfile->id,
                    'withdrawal_amount' => $amount,
                    'charge_amount' => $charge,
                    'description' => "Withdrawal processing charge from contractor"
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
                "Withdrawal Processed - " . ($bankSchedule->reference_id ?? $bankSchedule->id),
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
                'description' => "Withdrawal processed: " . ($bankSchedule->reference_id ?? $bankSchedule->id) . " (Amount + Charge)"
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
