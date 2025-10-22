<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Business;
use App\Services\MoneyTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestPaymentFlow extends Command
{
    protected $signature = 'payments:test-flow {--reset : Reset transactions before testing} {--limit=5 : Maximum number of transactions to process}';
    protected $description = 'Test the complete payment flow locally by simulating successful payments';

    public function handle()
    {
        $reset = $this->option('reset');
        $limit = $this->option('limit');
        
        Log::info('=== TESTING PAYMENT FLOW LOCALLY ===', [
            'timestamp' => now(),
            'command' => 'payments:test-flow',
            'reset' => $reset,
            'limit' => $limit,
            'server' => gethostname()
        ]);

        $this->info('🧪 Starting Payment Flow Test...');
        $this->newLine();

        // Step 1: Show current status
        $this->showCurrentStatus();

        // Step 2: Reset if requested
        if ($reset) {
            $this->info('🔄 Resetting transactions for fresh test...');
            $this->call('payments:reset-for-testing', ['--confirm' => true]);
            $this->newLine();
        }

        // Step 3: Simulate successful payments
        $this->info('🎯 Simulating successful payments...');
        $this->call('payments:simulate-success', ['--limit' => $limit]);
        $this->newLine();

        // Step 4: Show final status
        $this->showFinalStatus();

        // Step 5: Show suspense accounts
        $this->showSuspenseAccounts();

        $this->newLine();
        $this->info('✅ Payment flow test completed!');
        
        Log::info('=== PAYMENT FLOW TEST COMPLETED ===', [
            'timestamp' => now()
        ]);
    }

    private function showCurrentStatus()
    {
        $this->info('📊 Current Transaction Status:');
        
        $pendingCount = Transaction::where('status', 'pending')->count();
        $completedCount = Transaction::where('status', 'completed')->count();
        $failedCount = Transaction::where('status', 'failed')->count();
        
        $this->line("  • Pending: {$pendingCount}");
        $this->line("  • Completed: {$completedCount}");
        $this->line("  • Failed: {$failedCount}");
        
        // Show pending transactions
        $pendingTransactions = Transaction::where('status', 'pending')
            ->with(['client', 'business'])
            ->limit(5)
            ->get();
            
        if ($pendingTransactions->count() > 0) {
            $this->newLine();
            $this->info('📋 Pending Transactions:');
            foreach ($pendingTransactions as $transaction) {
                $this->line("  • ID: {$transaction->id} | Amount: {$transaction->amount} UGX | Client: " . ($transaction->client->name ?? 'Unknown'));
            }
        }
        
        $this->newLine();
    }

    private function showFinalStatus()
    {
        $this->info('📊 Final Transaction Status:');
        
        $pendingCount = Transaction::where('status', 'pending')->count();
        $completedCount = Transaction::where('status', 'completed')->count();
        $failedCount = Transaction::where('status', 'failed')->count();
        
        $this->line("  • Pending: {$pendingCount}");
        $this->line("  • Completed: {$completedCount}");
        $this->line("  • Failed: {$failedCount}");
        
        $this->newLine();
    }

    private function showSuspenseAccounts()
    {
        $this->info('💰 Suspense Account Balances:');
        
        // Get all suspense accounts
        $suspenseAccounts = \App\Models\MoneyAccount::whereIn('type', [
            'package_suspense_account',
            'general_suspense_account', 
            'kashtre_suspense_account'
        ])->get();
        
        $totalPackageSuspense = $suspenseAccounts->where('type', 'package_suspense_account')->sum('balance');
        $totalGeneralSuspense = $suspenseAccounts->where('type', 'general_suspense_account')->sum('balance');
        $totalKashtreSuspense = $suspenseAccounts->where('type', 'kashtre_suspense_account')->sum('balance');
        $totalSuspense = $totalPackageSuspense + $totalGeneralSuspense + $totalKashtreSuspense;
        
        $this->line("  • Package Suspense: " . number_format($totalPackageSuspense, 0) . " UGX");
        $this->line("  • General Suspense: " . number_format($totalGeneralSuspense, 0) . " UGX");
        $this->line("  • Kashtre Suspense: " . number_format($totalKashtreSuspense, 0) . " UGX");
        $this->line("  • Total Suspense: " . number_format($totalSuspense, 0) . " UGX");
        
        // Show individual suspense accounts
        if ($suspenseAccounts->count() > 0) {
            $this->newLine();
            $this->info('📋 Individual Suspense Accounts:');
            foreach ($suspenseAccounts as $account) {
                $clientName = $account->client->name ?? 'Business';
                $this->line("  • {$account->type}: " . number_format($account->balance, 0) . " UGX (Client: {$clientName})");
            }
        }
        
        $this->newLine();
    }
}
