<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\BalanceHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SimulatePayBackPayments extends Command
{
    protected $signature = 'payments:simulate-pay-back {--limit=10 : Maximum number of transactions to process}';
    protected $description = 'Simulate successful pay-back payments for local testing - treats all pending pay-back payments as successful';

    public function handle()
    {
        $limit = $this->option('limit');
        
        Log::info('=== SIMULATING SUCCESSFUL PAY-BACK PAYMENTS FOR LOCAL TESTING ===', [
            'timestamp' => now(),
            'command' => 'payments:simulate-pay-back',
            'limit' => $limit,
            'server' => gethostname(),
            'php_version' => PHP_VERSION
        ]);

        // Get all pending pay-back transactions (service = 'pp_payment')
        $pendingPayBackTransactions = Transaction::where('status', 'pending')
            ->where('service', 'pp_payment')
            ->where('method', 'mobile_money')
            ->with(['business', 'client', 'invoice'])
            ->limit($limit)
            ->get();

        Log::info('Found pending pay-back transactions to simulate as successful', [
            'count' => $pendingPayBackTransactions->count(),
            'limit' => $limit
        ]);

        if ($pendingPayBackTransactions->count() === 0) {
            $this->info('No pending pay-back transactions found to simulate.');
            Log::info('No pending pay-back transactions found to simulate');
            return;
        }

        $processedCount = 0;

        foreach ($pendingPayBackTransactions as $transaction) {
            try {
                Log::info("🎉 SIMULATING PAY-BACK PAYMENT SUCCESS - Processing transaction {$transaction->id}", [
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
                    'payment_status' => 'Paid',
                    'updated_at' => now()
                ]);

                Log::info("Transaction status updated to completed (simulated)", [
                    'transaction_id' => $transaction->id
                ]);

                // Complete the pay-back payment using the same logic as CompletePayBackPayments
                $this->completePayBackPayment($transaction);

                DB::commit();
                $processedCount++;

                Log::info("🎉 === SIMULATED PAY-BACK PAYMENT SUCCEEDED - Transaction processing completed ===", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'invoice_id' => $transaction->invoice_id
                ]);

                $this->info("✅ Simulated successful pay-back payment for transaction {$transaction->id} (Amount: {$transaction->amount} UGX)");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error simulating pay-back payment for transaction {$transaction->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error("❌ Error simulating pay-back payment for transaction {$transaction->id}: " . $e->getMessage());
            }
        }

        Log::info('=== PAY-BACK SIMULATION COMPLETED ===', [
            'processed_count' => $processedCount,
            'total_found' => $pendingPayBackTransactions->count()
        ]);

        $this->info("🎉 Pay-back simulation completed! Processed {$processedCount} transactions as successful payments.");
    }

    /**
     * Complete pay-back payment when mobile money transaction succeeds (simulated)
     */
    private function completePayBackPayment($transaction)
    {
        try {
            Log::info("=== COMPLETING PAY-BACK PAYMENT (SIMULATED) ===", [
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id,
                'amount' => $transaction->amount,
                'client_id' => $transaction->client_id
            ]);

            $invoice = Invoice::find($transaction->invoice_id);
            if (!$invoice) {
                Log::error("Invoice not found for pay-back transaction", [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $transaction->invoice_id
                ]);
                return;
            }

            $client = \App\Models\Client::find($transaction->client_id);
            if (!$client) {
                Log::error("Client not found for pay-back transaction", [
                    'transaction_id' => $transaction->id,
                    'client_id' => $transaction->client_id
                ]);
                return;
            }

            $business = $client->business;
            $totalAmount = $transaction->amount;

            // Extract entry IDs from invoice notes
            $notes = $invoice->notes ?? '';
            preg_match('/Entry IDs: ([\d,]+)/', $notes, $matches);
            $entryIds = $matches ? explode(',', $matches[1]) : [];

            if (empty($entryIds)) {
                Log::error("Entry IDs not found in invoice notes for pay-back", [
                    'invoice_id' => $invoice->id,
                    'notes' => $notes
                ]);
                return;
            }

            // Get the selected entries
            $entries = BalanceHistory::whereIn('id', $entryIds)
                ->where('client_id', $client->id)
                ->where('transaction_type', 'debit')
                ->with(['invoice'])
                ->get();

            if ($entries->isEmpty()) {
                Log::error("No entries found for pay-back payment", [
                    'entry_ids' => $entryIds,
                    'client_id' => $client->id
                ]);
                return;
            }

            // Update invoice
            $invoice->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'amount_paid' => $totalAmount,
                'balance_due' => 0,
            ]);

            Log::info("Payment invoice updated for pay-back (simulated)", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            // Credit client account (reduces debt) - Create individual credit entry for each selected item
            // Money was already transferred when services were delivered (tagged as pending_payment)
            // We just need to update payment status and credit the client
            foreach ($entries as $entry) {
                $entryAmount = abs($entry->change_amount);
                
                // Create individual credit entry for each selected item
                BalanceHistory::recordCredit(
                    $client,
                    $entryAmount,
                    $entry->description, // Use the item's description
                    $invoice->invoice_number,
                    "Payment for: {$entry->description} - Invoice #{$invoice->invoice_number}",
                    'mobile_money',
                    $invoice->id,
                    'paid'
                );
                
                Log::info("Created individual credit entry for selected item (simulated)", [
                    'entry_id' => $entry->id,
                    'description' => $entry->description,
                    'amount' => $entryAmount,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }

            // Update client balance (reduce negative balance)
            $client->increment('balance', $totalAmount);

            // Update payment_status and payment_method ONLY for selected entries that were paid for
            // $entries contains only the selected items from $entryIds
            foreach ($entries as $entry) {
                // Only update the selected entry
                $entry->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'mobile_money',
                ]);
                
                Log::info("Updated selected BalanceHistory entry payment status for pay-back (simulated)", [
                    'entry_id' => $entry->id,
                    'description' => $entry->description,
                    'amount' => abs($entry->change_amount),
                    'payment_status' => 'paid',
                    'payment_method' => 'mobile_money',
                    'note' => 'This entry was selected and paid for'
                ]);
            }

            Log::info("Updated all selected BalanceHistory entries payment status for pay-back (simulated)", [
                'entry_ids' => $entryIds,
                'total_selected_entries' => count($entries),
                'payment_status' => 'paid',
                'payment_method' => 'mobile_money',
            ]);

            // Update corresponding BusinessBalanceHistory records ONLY for selected items that were paid for
            // Loop through only the selected entries
            foreach ($entries as $entry) {
                if (!$entry->invoice) continue;
                
                $entryAmount = abs($entry->change_amount);
                $isServiceCharge = stripos($entry->description, 'service fee') !== false || 
                                   stripos($entry->description, 'service charge') !== false;
                
                if ($isServiceCharge) {
                    // Service charge goes to Kashtre (business_id = 1)
                    // Update each individual service charge entry in Kashtre
                    $kashtreBalanceHistories = \App\Models\BusinessBalanceHistory::where('business_id', 1)
                        ->where('reference_type', 'invoice')
                        ->where('reference_id', $entry->invoice_id)
                        ->where('type', 'credit')
                        ->where(function($query) use ($entryAmount) {
                            $query->whereBetween('amount', [$entryAmount - 0.01, $entryAmount + 0.01]);
                        })
                        ->where(function($query) {
                            $query->where('payment_status', 'pending_payment')
                                  ->orWhereNull('payment_status');
                        })
                        ->get();
                    
                    // Update only the BusinessBalanceHistory records that correspond to this selected entry
                    foreach ($kashtreBalanceHistories as $kbh) {
                        $kbh->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'mobile_money',
                        ]);
                        
                        Log::info("Updated selected Kashtre service charge entry payment status for pay-back (simulated)", [
                            'business_balance_history_id' => $kbh->id,
                            'selected_entry_id' => $entry->id,
                            'description' => $entry->description,
                            'amount' => $entryAmount,
                            'payment_status' => 'paid',
                            'payment_method' => 'mobile_money',
                            'note' => 'This service charge was selected and paid for'
                        ]);
                    }
                } else {
                    // Regular items go to business account
                    // Update only the BusinessBalanceHistory records that correspond to this selected entry
                    $businessBalanceHistories = \App\Models\BusinessBalanceHistory::where('business_id', $business->id)
                        ->where('reference_type', 'invoice')
                        ->where('reference_id', $entry->invoice_id)
                        ->where('type', 'credit')
                        ->where(function($query) use ($entryAmount) {
                            $query->whereBetween('amount', [$entryAmount - 0.01, $entryAmount + 0.01]);
                        })
                        ->where(function($query) {
                            $query->where('payment_status', 'pending_payment')
                                  ->orWhereNull('payment_status');
                        })
                        ->get();
                    
                    foreach ($businessBalanceHistories as $bbh) {
                        $bbh->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'mobile_money',
                        ]);
                        
                        Log::info("Updated selected business item entry payment status for pay-back (simulated)", [
                            'business_balance_history_id' => $bbh->id,
                            'selected_entry_id' => $entry->id,
                            'description' => $entry->description,
                            'amount' => $entryAmount,
                            'payment_status' => 'paid',
                            'payment_method' => 'mobile_money',
                            'note' => 'This item was selected and paid for'
                        ]);
                    }
                }
            }

            // Update original invoices
            $invoicesToUpdate = [];
            $paidAmountsByInvoice = [];
            
            foreach ($entries as $entry) {
                if ($entry->invoice) {
                    $invoiceId = $entry->invoice->id;
                    $entryAmount = abs($entry->change_amount);
                    
                    if (!isset($paidAmountsByInvoice[$invoiceId])) {
                        $paidAmountsByInvoice[$invoiceId] = 0;
                        $invoicesToUpdate[$invoiceId] = $entry->invoice;
                    }
                    $paidAmountsByInvoice[$invoiceId] += $entryAmount;
                }
            }

            foreach ($invoicesToUpdate as $invoiceId => $originalInvoice) {
                $paidAmount = $paidAmountsByInvoice[$invoiceId];
                
                // Update invoice
                $originalInvoice->increment('amount_paid', $paidAmount);
                $originalInvoice->decrement('balance_due', $paidAmount);
                
                // If invoice is fully paid, update payment status
                $originalInvoice->refresh();
                if ($originalInvoice->balance_due <= 0) {
                    $originalInvoice->update(['payment_status' => 'paid']);
                } elseif ($originalInvoice->amount_paid > 0) {
                    $originalInvoice->update(['payment_status' => 'partial']);
                }
            }

            Log::info("Pay-back payment completed successfully (simulated)", [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'entry_ids' => $entryIds,
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to complete pay-back payment (simulated)", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

}

