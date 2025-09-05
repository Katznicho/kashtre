<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\MoneyAccount;
use App\Models\BalanceHistory;
use Illuminate\Support\Facades\DB;

class EmptyClientBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:empty {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empty all client account balances and money in suspense accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚨 WARNING: This will empty ALL client balances and suspense account money!');
        $this->newLine();
        
        // Show current totals before emptying
        $this->showCurrentTotals();
        
        if (!$this->option('confirm')) {
            if (!$this->confirm('Are you sure you want to proceed? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->newLine();
        $this->info('Starting to empty balances...');
        
        try {
            DB::transaction(function () {
                // 1. Empty client balances in clients table
                $this->emptyClientBalances();
                
                // 2. Empty money accounts (suspense accounts)
                $this->emptyMoneyAccounts();
                
                // 3. Clear balance history
                $this->clearBalanceHistory();
            });
            
            $this->newLine();
            $this->info('✅ Successfully emptied all client balances and suspense accounts!');
            $this->showCurrentTotals();
            
        } catch (\Exception $e) {
            $this->error('❌ Error occurred: ' . $e->getMessage());
            $this->error('Operation rolled back due to error.');
        }
    }

    /**
     * Show current totals before emptying
     */
    private function showCurrentTotals()
    {
        $this->info('📊 Current Totals:');
        
        // Client balances
        $totalClientBalance = Client::sum('balance');
        $this->line("   • Total client balances: UGX " . number_format($totalClientBalance, 2));
        
        // Money accounts
        $totalMoneyAccountBalance = MoneyAccount::sum('balance');
        $this->line("   • Total money account balances: UGX " . number_format($totalMoneyAccountBalance, 2));
        
        // Balance history records
        $balanceHistoryCount = BalanceHistory::count();
        $this->line("   • Balance history records: " . number_format($balanceHistoryCount));
        
        $this->newLine();
    }

    /**
     * Empty client balances in clients table
     */
    private function emptyClientBalances()
    {
        $this->info('🔄 Emptying client balances...');
        
        $updatedClients = Client::where('balance', '>', 0)->update(['balance' => 0]);
        
        $this->line("   • Updated {$updatedClients} clients with zero balance");
    }

    /**
     * Empty money accounts (suspense accounts)
     */
    private function emptyMoneyAccounts()
    {
        $this->info('🔄 Emptying money accounts (suspense accounts)...');
        
        $updatedAccounts = MoneyAccount::where('balance', '>', 0)->update(['balance' => 0]);
        
        $this->line("   • Updated {$updatedAccounts} money accounts with zero balance");
        
        // Show breakdown by account type
        $accountTypes = MoneyAccount::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
            
        foreach ($accountTypes as $type) {
            $this->line("     - {$type->type}: {$type->count} accounts");
        }
    }

    /**
     * Clear balance history records
     */
    private function clearBalanceHistory()
    {
        $this->info('🔄 Clearing balance history...');
        
        $deletedRecords = BalanceHistory::count();
        BalanceHistory::truncate();
        
        $this->line("   • Deleted {$deletedRecords} balance history records");
    }
}
