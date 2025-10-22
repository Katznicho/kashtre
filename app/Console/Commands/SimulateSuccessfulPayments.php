<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Client;
use App\Services\MoneyTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SimulateSuccessfulPayments extends Command
{
    protected $signature = 'payments:simulate-success {--limit=10 : Maximum number of transactions to process}';
    protected $description = 'Simulate successful payments for local testing - treats all pending payments as successful';

    public function handle()
    {
        $limit = $this->option('limit');
        
        Log::info('=== SIMULATING SUCCESSFUL PAYMENTS FOR LOCAL TESTING ===', [
            'timestamp' => now(),
            'command' => 'payments:simulate-success',
            'limit' => $limit,
            'server' => gethostname(),
            'php_version' => PHP_VERSION
        ]);

        // Get all pending transactions
        $pendingTransactions = Transaction::where('status', 'pending')
            ->where('method', 'mobile_money')
            ->with(['business', 'client'])
            ->limit($limit)
            ->get();

        Log::info('Found pending transactions to simulate as successful', [
            'count' => $pendingTransactions->count(),
            'limit' => $limit
        ]);

        if ($pendingTransactions->count() === 0) {
            $this->info('No pending transactions found to simulate.');
            Log::info('No pending transactions found to simulate');
            return;
        }

        $processedCount = 0;
        $moneyTrackingService = new MoneyTrackingService();

        foreach ($pendingTransactions as $transaction) {
            try {
                Log::info("🎉 SIMULATING PAYMENT SUCCESS - Processing transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'invoice_id' => $transaction->invoice_id
                ]);

                DB::beginTransaction();
                
                // Update transaction status to completed
                $transaction->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);

                Log::info("Transaction status updated to completed (simulated)", [
                    'transaction_id' => $transaction->id
                ]);

                // Update related invoice if exists
                if ($transaction->invoice_id) {
                    $invoice = Invoice::find($transaction->invoice_id);
                    if ($invoice) {
                        Log::info("Found invoice for simulated payment completion", [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'current_status' => $invoice->status,
                            'payment_status' => $invoice->payment_status
                        ]);

                        // Update invoice status to paid
                        $invoice->update([
                            'status' => 'paid',
                            'payment_status' => 'paid'
                        ]);

                        Log::info("Invoice status updated to paid (simulated)", [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number
                        ]);
                        
                        // Process payment received to move money to suspense account
                        Log::info("Processing simulated payment received to move money to suspense account", [
                            'invoice_id' => $invoice->id,
                            'amount_paid' => $invoice->amount_paid,
                            'client_id' => $invoice->client_id
                        ]);
                        
                        $client = $invoice->client;
                        
                        // Move money to suspense account
                        $moneyTrackingService->processPaymentReceived(
                            $client,
                            $invoice->amount_paid,
                            $invoice->invoice_number,
                            'mobile_money',
                            [
                                'invoice_id' => $invoice->id,
                                'transaction_id' => $transaction->id,
                                'payment_methods' => $invoice->payment_methods ?? ['mobile_money'],
                                'simulated' => true
                            ]
                        );
                        
                        // Create balance statements after payment completion
                        Log::info("Creating balance statements after simulated payment completion", [
                            'invoice_id' => $invoice->id,
                            'items_count' => count($invoice->items ?? [])
                        ]);
                        
                        $balanceStatements = $moneyTrackingService->processPaymentCompleted($invoice, $invoice->items);
                        
                        Log::info("Balance statements created after simulated payment completion", [
                            'invoice_id' => $invoice->id,
                            'balance_statements_count' => count($balanceStatements)
                        ]);
                        
                        // Queue items at service points only after payment is completed
                        Log::info("Starting to queue items at service points (simulated)", [
                            'invoice_id' => $invoice->id,
                            'items_count' => count($invoice->items ?? [])
                        ]);
                        
                        $queuedItems = $this->queueItemsAtServicePoints($invoice, $invoice->items);
                        
                        Log::info("Items queued at service points completed (simulated)", [
                            'invoice_id' => $invoice->id,
                            'queued_items_count' => $queuedItems
                        ]);
                        
                        // Create package tracking records for package items
                        Log::info("Creating package tracking records (simulated)", [
                            'invoice_id' => $invoice->id,
                            'items_count' => count($invoice->items ?? [])
                        ]);
                        
                        $this->createPackageTrackingRecords($invoice, $invoice->items);
                        
                        Log::info("Package tracking records created (simulated)", [
                            'invoice_id' => $invoice->id
                        ]);
                    } else {
                        Log::warning("Invoice not found for transaction", [
                            'transaction_id' => $transaction->id,
                            'invoice_id' => $transaction->invoice_id
                        ]);
                    }
                } else {
                    Log::warning("No invoice_id found for transaction", [
                        'transaction_id' => $transaction->id
                    ]);
                }

                // Update client balance if needed
                if ($transaction->client_id) {
                    $client = Client::find($transaction->client_id);
                    if ($client) {
                        Log::info("Simulated payment succeeded for client", [
                            'client_id' => $client->id,
                            'client_name' => $client->name,
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount
                        ]);
                    } else {
                        Log::warning("Client not found for transaction", [
                            'transaction_id' => $transaction->id,
                            'client_id' => $transaction->client_id
                        ]);
                    }
                }

                DB::commit();
                $processedCount++;

                Log::info("=== SIMULATED PAYMENT SUCCEEDED - Transaction processing completed ===", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount
                ]);

                $this->info("✅ Simulated successful payment for transaction {$transaction->id} (Amount: {$transaction->amount} UGX)");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error simulating payment for transaction {$transaction->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error("❌ Error simulating payment for transaction {$transaction->id}: " . $e->getMessage());
            }
        }

        Log::info('=== SIMULATION COMPLETED ===', [
            'processed_count' => $processedCount,
            'total_found' => $pendingTransactions->count()
        ]);

        $this->info("🎉 Simulation completed! Processed {$processedCount} transactions as successful payments.");
    }

    private function queueItemsAtServicePoints($invoice, $items)
    {
        $queuedCount = 0;
        
        if (!$items || !is_array($items)) {
            Log::warning("No items found for invoice", ['invoice_id' => $invoice->id]);
            return $queuedCount;
        }

        foreach ($items as $item) {
            try {
                // Simulate queuing logic here
                Log::info("Simulating item queuing at service point", [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item['id'] ?? 'unknown',
                    'item_name' => $item['name'] ?? 'unknown'
                ]);
                
                $queuedCount++;
            } catch (\Exception $e) {
                Log::error("Error queuing item at service point", [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $queuedCount;
    }

    private function createPackageTrackingRecords($invoice, $items)
    {
        if (!$items || !is_array($items)) {
            Log::warning("No items found for package tracking", ['invoice_id' => $invoice->id]);
            return;
        }

        foreach ($items as $item) {
            try {
                // Simulate package tracking creation here
                Log::info("Simulating package tracking record creation", [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item['id'] ?? 'unknown',
                    'item_name' => $item['name'] ?? 'unknown'
                ]);
            } catch (\Exception $e) {
                Log::error("Error creating package tracking record", [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
