<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\BalanceHistory;
use App\Payments\YoAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CompletePayBackPayments extends Command
{
    protected $signature = 'payments:complete-pay-back'; 
    protected $description = 'Check and complete pay-back mobile money payments';

    public function handle()
    {
        Log::info('=== CRON JOB STARTED: CompletePayBackPayments ===', [
            'timestamp' => now(),
            'command' => 'payments:complete-pay-back',
        ]);

        // Get all pending pay-back transactions (service = 'pp_payment')
        $pendingPayBackTransactions = Transaction::where('status', 'pending')
            ->where('service', 'pp_payment')
            ->where('method', 'mobile_money')
            ->where('provider', 'yo')
            ->whereNotNull('external_reference')
            ->with(['business', 'client', 'invoice'])
            ->get();

        Log::info('Found pending pay-back mobile money transactions', [
            'count' => $pendingPayBackTransactions->count(),
        ]);

        if ($pendingPayBackTransactions->isEmpty()) {
            Log::info('No pending pay-back transactions found - CRON JOB EXITING');
            return;
        }

        $yoPayments = new YoAPI(config('payments.yo_username'), config('payments.yo_password'));

        foreach ($pendingPayBackTransactions as $index => $transaction) {
            try {
                Log::info("=== PROCESSING PAY-BACK TRANSACTION " . ($index + 1) . " OF " . $pendingPayBackTransactions->count() . " ===", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'invoice_id' => $transaction->invoice_id,
                ]);

                // Check payment status with YoAPI
                $statusCheck = $yoPayments->ac_transaction_check_status($transaction->external_reference);

                Log::info("YoAPI status check response for pay-back transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'response' => $statusCheck
                ]);

                if (isset($statusCheck['TransactionStatus'])) {
                    if ($statusCheck['TransactionStatus'] === 'SUCCEEDED') {
                        Log::info("🎉 PAY-BACK PAYMENT SUCCEEDED - Processing transaction {$transaction->id} ===", [
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount,
                        ]);

                        DB::beginTransaction();
                        
                        try {
                            // Update transaction status
                            $transaction->update([
                                'status' => 'completed',
                                'payment_status' => 'Paid',
                                'updated_at' => now()
                            ]);

                            // Complete the pay-back payment
                            $this->completePayBackPayment($transaction);

                            DB::commit();

                            Log::info("=== PAY-BACK PAYMENT COMPLETED SUCCESSFULLY ===", [
                                'transaction_id' => $transaction->id,
                            ]);

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error("Failed to complete pay-back payment for transaction {$transaction->id}", [
                                'transaction_id' => $transaction->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }

                    } elseif ($statusCheck['TransactionStatus'] === 'PENDING') {
                        Log::info("Pay-back transaction still pending - no action taken", [
                            'transaction_id' => $transaction->id,
                        ]);
                    } elseif ($statusCheck['TransactionStatus'] === 'FAILED') {
                        // Update transaction status to failed
                        $transaction->update([
                            'status' => 'failed',
                            'updated_at' => now()
                        ]);

                        Log::info("Pay-back transaction ID {$transaction->id} updated to FAILED", [
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                } else {
                    Log::warning("No valid status returned for pay-back transaction ID: {$transaction->id}", [
                        'transaction_id' => $transaction->id,
                        'response' => $statusCheck
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("Error checking status for pay-back transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('=== CRON JOB COMPLETED: CompletePayBackPayments ===', [
            'total_checked' => $pendingPayBackTransactions->count(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Complete pay-back payment when mobile money transaction succeeds
     */
    private function completePayBackPayment($transaction)
    {
        try {
            Log::info("=== COMPLETING PAY-BACK PAYMENT ===", [
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

            Log::info("Payment invoice updated for pay-back", [
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
                
                Log::info("Created individual credit entry for selected item", [
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
                
                Log::info("Updated selected BalanceHistory entry payment status for pay-back", [
                    'entry_id' => $entry->id,
                    'description' => $entry->description,
                    'amount' => abs($entry->change_amount),
                    'payment_status' => 'paid',
                    'payment_method' => 'mobile_money',
                    'note' => 'This entry was selected and paid for'
                ]);
            }

            Log::info("Updated all selected BalanceHistory entries payment status for pay-back", [
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
                        
                        Log::info("Updated selected Kashtre service charge entry payment status for pay-back", [
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
                    // Update each individual business item entry
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
                    
                    // Update only the BusinessBalanceHistory records that correspond to this selected entry
                    foreach ($businessBalanceHistories as $bbh) {
                        $bbh->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'mobile_money',
                        ]);
                        
                        Log::info("Updated selected business item entry payment status for pay-back", [
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

            // Update original invoices - only for selected items that were paid for
            $invoicesToUpdate = [];
            $paidAmountsByInvoice = [];
            
            // Only process invoices for selected entries
            foreach ($entries as $entry) {
                if ($entry->invoice) {
                    $invoiceId = $entry->invoice->id;
                    $entryAmount = abs($entry->change_amount);
                    
                    if (!isset($paidAmountsByInvoice[$invoiceId])) {
                        $paidAmountsByInvoice[$invoiceId] = 0;
                        $invoicesToUpdate[$invoiceId] = $entry->invoice;
                    }
                    // Only add amount for this selected entry
                    $paidAmountsByInvoice[$invoiceId] += $entryAmount;
                }
            }

            // Update invoices only with amounts from selected entries
            foreach ($invoicesToUpdate as $invoiceId => $originalInvoice) {
                $paidAmount = $paidAmountsByInvoice[$invoiceId];
                
                // Update invoice with only the selected items' amounts
                $originalInvoice->increment('amount_paid', $paidAmount);
                $originalInvoice->decrement('balance_due', $paidAmount);
                
                // If invoice is fully paid, update payment status
                $originalInvoice->refresh();
                if ($originalInvoice->balance_due <= 0) {
                    $originalInvoice->update(['payment_status' => 'paid']);
                } elseif ($originalInvoice->amount_paid > 0) {
                    $originalInvoice->update(['payment_status' => 'partial']);
                }
                
                Log::info("Updated invoice with selected items payment", [
                    'invoice_id' => $invoiceId,
                    'paid_amount' => $paidAmount,
                    'note' => 'Only selected items were paid for'
                ]);
            }

            Log::info("Pay-back payment completed successfully", [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'entry_ids' => $entryIds,
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to complete pay-back payment", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

}

