<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResetTransactionsForTesting extends Command
{
    protected $signature = 'payments:reset-for-testing {--confirm : Confirm the reset operation}';
    protected $description = 'Reset completed transactions back to pending for testing purposes';

    public function handle()
    {
        if (!$this->option('confirm')) {
            $this->error('❌ This command requires --confirm flag to proceed');
            $this->info('Usage: php artisan payments:reset-for-testing --confirm');
            return;
        }

        Log::info('=== RESETTING TRANSACTIONS FOR TESTING ===', [
            'timestamp' => now(),
            'command' => 'payments:reset-for-testing',
            'server' => gethostname()
        ]);

        // Get all completed transactions
        $completedTransactions = Transaction::where('status', 'completed')
            ->with(['business', 'client'])
            ->get();

        Log::info('Found completed transactions to reset', [
            'count' => $completedTransactions->count()
        ]);

        if ($completedTransactions->count() === 0) {
            $this->info('No completed transactions found to reset.');
            Log::info('No completed transactions found to reset');
            return;
        }

        $resetCount = 0;

        foreach ($completedTransactions as $transaction) {
            try {
                DB::beginTransaction();

                // Reset transaction status to pending
                $transaction->update([
                    'status' => 'pending',
                    'updated_at' => now()
                ]);

                // Reset related invoice if exists
                if ($transaction->invoice_id) {
                    $invoice = Invoice::find($transaction->invoice_id);
                    if ($invoice) {
                        $invoice->update([
                            'status' => 'pending',
                            'payment_status' => 'pending'
                        ]);

                        Log::info("Reset invoice status to pending", [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number
                        ]);
                    }
                }

                DB::commit();
                $resetCount++;

                Log::info("Reset transaction to pending", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount
                ]);

                $this->info("🔄 Reset transaction {$transaction->id} to pending status");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error resetting transaction {$transaction->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error("❌ Error resetting transaction {$transaction->id}: " . $e->getMessage());
            }
        }

        Log::info('=== RESET COMPLETED ===', [
            'reset_count' => $resetCount,
            'total_found' => $completedTransactions->count()
        ]);

        $this->info("🔄 Reset completed! Reset {$resetCount} transactions back to pending status.");
    }
}
