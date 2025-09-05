<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BalanceHistory;
use App\Models\BusinessBalanceHistory;
use App\Models\ContractorBalanceHistory;
use App\Models\MoneyTransfer;
use App\Models\ServiceDeliveryQueue;
use App\Models\MoneyAccount;
use App\Models\ContractorProfile;

class ResetAccountStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:account-statements {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all account statements and related data for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('This will delete ALL account statements, money transfers, and reset all balances. Are you sure?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('🚨 STARTING ACCOUNT STATEMENT RESET 🚨');
        $this->info('==========================================');

        try {
            // Reset Service Delivery Queues finalization
            $this->info('Resetting service delivery queue finalization...');
            ServiceDeliveryQueue::query()->update([
                'is_finalized' => false,
                'finalized_at' => null,
                'finalized_by_user_id' => null
            ]);

            // Clear all balance histories
            $this->info('Clearing client balance histories...');
            $clientCount = BalanceHistory::count();
            BalanceHistory::truncate();
            $this->info("Deleted {$clientCount} client balance history records");

            $this->info('Clearing business balance histories...');
            $businessCount = BusinessBalanceHistory::count();
            BusinessBalanceHistory::truncate();
            $this->info("Deleted {$businessCount} business balance history records");

            $this->info('Clearing contractor balance histories...');
            $contractorCount = ContractorBalanceHistory::count();
            ContractorBalanceHistory::truncate();
            $this->info("Deleted {$contractorCount} contractor balance history records");

            // Clear money transfers
            $this->info('Clearing money transfers...');
            $transferCount = MoneyTransfer::count();
            MoneyTransfer::truncate();
            $this->info("Deleted {$transferCount} money transfer records");

            // Reset all money account balances to 0
            $this->info('Resetting money account balances...');
            MoneyAccount::query()->update(['balance' => 0]);
            $this->info('All money account balances reset to 0');

            // Reset all contractor profile account balances to 0
            $this->info('Resetting contractor profile account balances...');
            $contractorProfileCount = ContractorProfile::count();
            ContractorProfile::query()->update(['account_balance' => 0]);
            $this->info("Reset {$contractorProfileCount} contractor profile account balances to 0");

            $this->info('');
            $this->info('✅ ACCOUNT STATEMENT RESET COMPLETED SUCCESSFULLY ✅');
            $this->info('==========================================');
            $this->info('All balance histories, money transfers, and account balances have been cleared.');
            $this->info('Service delivery queues have been reset and can be finalized again.');
            $this->info('');
            $this->info('You can now test the new money movement flow:');
            $this->info('1. Update item statuses (temporary)');
            $this->info('2. Press "Save and Exit" to finalize and process money movements');
            $this->info('3. Check the various account statements for the new transactions');

        } catch (\Exception $e) {
            $this->error('❌ RESET FAILED ❌');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Some operations may have completed, others may have failed.');
        }
    }
}
