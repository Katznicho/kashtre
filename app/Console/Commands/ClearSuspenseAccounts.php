<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MoneyAccount;
use App\Models\MoneyTransfer;
use App\Models\PackageTracking;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearSuspenseAccounts extends Command
{
    protected $signature = 'suspense:clear {--force : Force clear without confirmation}';
    protected $description = 'Clear all suspense account data for fresh testing';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will clear ALL suspense account data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('🧹 Clearing all suspense account data...');

        try {
            DB::beginTransaction();

            // 1. Clear all suspense account balances
            $this->info('📊 Resetting suspense account balances...');
            MoneyAccount::whereIn('type', [
                'package_suspense_account',
                'general_suspense_account',
                'kashtre_suspense_account',
                'client_suspense_account'
            ])->update(['balance' => 0]);

            // 2. Delete all suspense-related money transfers
            $this->info('💸 Deleting suspense money transfers...');
            $suspenseTransfers = MoneyTransfer::whereHas('toAccount', function($query) {
                $query->whereIn('type', [
                    'package_suspense_account',
                    'general_suspense_account',
                    'kashtre_suspense_account'
                ]);
            })->orWhereHas('fromAccount', function($query) {
                $query->where('type', 'client_suspense_account');
            });

            $transferCount = $suspenseTransfers->count();
            $suspenseTransfers->delete();

            // 3. Reset package tracking money movement status
            $this->info('📦 Resetting package tracking money movement status...');
            PackageTracking::where('package_money_moved', true)->update([
                'package_money_moved' => false,
                'money_moved_at' => null,
                'money_movement_notes' => null
            ]);

            // 4. Reset pending transactions (optional)
            if ($this->confirm('Reset pending transactions to allow re-testing?')) {
                $this->info('🔄 Resetting pending transactions...');
                Transaction::where('status', 'pending')->update(['status' => 'failed']);
            }

            DB::commit();

            $this->info("✅ Successfully cleared suspense account data:");
            $this->line("   • Reset all suspense account balances to 0");
            $this->line("   • Deleted {$transferCount} suspense money transfers");
            $this->line("   • Reset package tracking money movement status");
            $this->line("   • Ready for fresh testing!");

            Log::info("Suspense accounts cleared for fresh testing", [
                'transfers_deleted' => $transferCount,
                'cleared_by' => 'ClearSuspenseAccounts command'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error clearing suspense accounts: " . $e->getMessage());
            Log::error("Failed to clear suspense accounts", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}