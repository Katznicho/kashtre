<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Client;
use App\Payments\YoAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckPaymentStatus extends Command
{
    protected $signature = 'payments:check-status'; 
    protected $description = 'Check and update payment statuses from YoPayments asynchronously';

    public function handle()
    {
        // Get all pending transactions that have YoAPI references
        $pendingTransactions = Transaction::where('status', 'pending')
            ->whereNotNull('reference')
            ->where('method', 'mobile_money')
            ->where('provider', 'yo')
            ->with(['business', 'client'])
            ->get();

        if ($pendingTransactions->isEmpty()) {
            Log::info('No pending mobile money transactions found for status check');
            return;
        }

        $yoPayments = new YoAPI(config('payments.yo_username'), config('payments.yo_password'));

        foreach ($pendingTransactions as $transaction) {
            try {
                if (!$transaction->external_reference) {
                    Log::warning("No external reference found for transaction ID: {$transaction->id}");
                    continue;
                }

                // Check payment status with YoAPI using external_reference
                $statusCheck = $yoPayments->ac_transaction_check_status($transaction->external_reference);

                Log::info("YoAPI status check response for transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'response' => $statusCheck
                ]);

                if (isset($statusCheck['TransactionStatus'])) {
                    DB::beginTransaction();
                    
                    try {
                        if ($statusCheck['TransactionStatus'] === 'SUCCEEDED') {
                            // Update transaction status
                            $transaction->update([
                                'status' => 'completed',
                                'updated_at' => now()
                            ]);

                            // Update related invoice if exists
                            if ($transaction->invoice_id) {
                                $invoice = Invoice::find($transaction->invoice_id);
                                if ($invoice) {
                                    $invoice->update(['status' => 'paid']);
                                }
                            }

                            // Update client balance if needed
                            if ($transaction->client_id) {
                                $client = Client::find($transaction->client_id);
                                if ($client) {
                                    // The money tracking service should have already handled the balance
                                    // but we can log this for audit purposes
                                    Log::info("Payment succeeded for client {$client->name}", [
                                        'client_id' => $client->id,
                                        'transaction_id' => $transaction->id,
                                        'amount' => $transaction->amount
                                    ]);
                                }
                            }

                            Log::info("Transaction ID {$transaction->id} updated to SUCCEEDED", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'amount' => $transaction->amount
                            ]);

                        } elseif ($statusCheck['TransactionStatus'] === 'FAILED') {
                            // Update transaction status to failed
                            $transaction->update([
                                'status' => 'failed',
                                'updated_at' => now()
                            ]);

                            // Update related invoice if exists
                            if ($transaction->invoice_id) {
                                $invoice = Invoice::find($transaction->invoice_id);
                                if ($invoice) {
                                    $invoice->update(['status' => 'failed']);
                                }
                            }

                            Log::info("Transaction ID {$transaction->id} updated to FAILED", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'amount' => $transaction->amount
                            ]);
                        }

                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to update transaction {$transaction->id} status", [
                            'transaction_id' => $transaction->id,
                            'error' => $e->getMessage(),
                            'status_response' => $statusCheck
                        ]);
                    }

                } else {
                    Log::warning("No valid status returned for transaction ID: {$transaction->id}", [
                        'transaction_id' => $transaction->id,
                        'reference' => $transaction->reference,
                        'response' => $statusCheck
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("Error checking status for transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Payment status check completed', [
            'total_checked' => $pendingTransactions->count(),
            'timestamp' => now()
        ]);
    }
}
