<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Item;
use App\Payments\YoAPI;
use App\Services\MoneyTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckPaymentStatus extends Command
{
    protected $signature = 'payments:check-status'; 
    protected $description = 'Check and update YoAPI payment statuses using external_reference field';

    public function handle()
    {
        Log::info('=== CRON JOB STARTED: CheckPaymentStatus ===', [
            'timestamp' => now(),
            'command' => 'payments:check-status',
            'server' => gethostname(),
            'php_version' => PHP_VERSION
        ]);

        // Get all pending transactions that have YoAPI references
        $pendingTransactions = Transaction::where('status', 'pending')
            ->whereNotNull('reference')
            ->where('method', 'mobile_money')
            ->where('provider', 'yo')
            ->with(['business', 'client'])
            ->get();

        Log::info('Found pending mobile money transactions', [
            'count' => $pendingTransactions->count(),
            'transactions' => $pendingTransactions->map(function($t) {
                return [
                    'id' => $t->id,
                    'reference' => $t->reference,
                    'external_reference' => $t->external_reference,
                    'amount' => $t->amount,
                    'client_id' => $t->client_id,
                    'invoice_id' => $t->invoice_id,
                    'created_at' => $t->created_at->toDateTimeString()
                ];
            })->toArray()
        ]);

        if ($pendingTransactions->isEmpty()) {
            Log::info('No pending mobile money transactions found for status check - CRON JOB EXITING');
            return;
        }

        $yoPayments = new YoAPI(config('payments.yo_username'), config('payments.yo_password'));

        foreach ($pendingTransactions as $index => $transaction) {
            try {
                Log::info("=== PROCESSING TRANSACTION " . ($index + 1) . " OF " . $pendingTransactions->count() . " ===", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'invoice_id' => $transaction->invoice_id,
                    'created_at' => $transaction->created_at->toDateTimeString()
                ]);

                if (!$transaction->external_reference) {
                    Log::warning("No external reference found for transaction ID: {$transaction->id} - SKIPPING");
                    continue;
                }

                Log::info("Checking payment status with YoAPI", [
                    'transaction_id' => $transaction->id,
                    'external_reference' => $transaction->external_reference
                ]);

                // Check payment status with YoAPI using external_reference
                $statusCheck = $yoPayments->ac_transaction_check_status($transaction->external_reference);

                Log::info("YoAPI status check response for transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'response' => $statusCheck
                ]);

                if (isset($statusCheck['TransactionStatus'])) {
                    Log::info("Transaction status received from YoAPI", [
                        'transaction_id' => $transaction->id,
                        'status' => $statusCheck['TransactionStatus'],
                        'full_response' => $statusCheck
                    ]);

                    DB::beginTransaction();
                    
                    try {
                        if ($statusCheck['TransactionStatus'] === 'SUCCEEDED') {
                            Log::info("🎉 AUTOMATIC PAYMENT COMPLETION DETECTED - Transaction will be completed automatically", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'amount' => $transaction->amount,
                                'client_id' => $transaction->client_id,
                                'invoice_id' => $transaction->invoice_id
                            ]);
                            Log::info("=== PAYMENT SUCCEEDED - Processing transaction {$transaction->id} ===", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'external_reference' => $transaction->external_reference,
                                'amount' => $transaction->amount,
                                'client_id' => $transaction->client_id,
                                'invoice_id' => $transaction->invoice_id
                            ]);

                            // Update transaction status
                            $transaction->update([
                                'status' => 'completed',
                                'updated_at' => now()
                            ]);

                            Log::info("Transaction status updated to completed", [
                                'transaction_id' => $transaction->id
                            ]);

                            // Update related invoice if exists
                            if ($transaction->invoice_id) {
                                $invoice = Invoice::find($transaction->invoice_id);
                                if ($invoice) {
                                    Log::info("Found invoice for payment completion", [
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

                                    Log::info("Invoice status updated to paid", [
                                        'invoice_id' => $invoice->id,
                                        'invoice_number' => $invoice->invoice_number
                                    ]);
                                    
                                    // First, process payment received to move money to suspense account
                                    Log::info("Processing payment received to move money to suspense account", [
                                        'invoice_id' => $invoice->id,
                                        'amount_paid' => $invoice->amount_paid,
                                        'client_id' => $invoice->client_id
                                    ]);
                                    
                                    $moneyTrackingService = new MoneyTrackingService();
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
                                            'payment_methods' => $invoice->payment_methods ?? ['mobile_money']
                                        ]
                                    );
                                    
                                    // Then create balance statements after payment completion
                                    Log::info("Creating balance statements after payment completion", [
                                        'invoice_id' => $invoice->id,
                                        'items_count' => count($invoice->items ?? [])
                                    ]);
                                    
                                    $balanceStatements = $moneyTrackingService->processPaymentCompleted($invoice, $invoice->items);
                                    
                                    Log::info("Balance statements created after payment completion", [
                                        'invoice_id' => $invoice->id,
                                        'balance_statements_count' => count($balanceStatements)
                                    ]);
                                    
                                    // Queue items at service points only after payment is completed
                                    Log::info("Starting to queue items at service points", [
                                        'invoice_id' => $invoice->id,
                                        'items_count' => count($invoice->items ?? [])
                                    ]);
                                    
                                    $queuedItems = $this->queueItemsAtServicePoints($invoice, $invoice->items);
                                    
                                    Log::info("Items queued at service points completed", [
                                        'invoice_id' => $invoice->id,
                                        'queued_items_count' => $queuedItems
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
                                    Log::info("Payment succeeded for client", [
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

                            Log::info("=== PAYMENT SUCCEEDED - Transaction processing completed ===", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'amount' => $transaction->amount
                            ]);

                        } elseif ($statusCheck['TransactionStatus'] === 'PENDING') {
                            Log::info("Transaction still pending - no action taken", [
                                'transaction_id' => $transaction->id,
                                'reference' => $transaction->reference,
                                'status' => 'PENDING'
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

        Log::info('=== CRON JOB COMPLETED: CheckPaymentStatus ===', [
            'total_checked' => $pendingTransactions->count(),
            'timestamp' => now(),
            'command' => 'payments:check-status',
            'summary' => [
                'transactions_processed' => $pendingTransactions->count(),
                'server' => gethostname(),
                'execution_time' => now()->toDateTimeString()
            ]
        ]);
    }

    /**
     * Queue items at their respective service points
     */
    private function queueItemsAtServicePoints($invoice, $items)
    {
        $queuedCount = 0;
        
        Log::info("=== STARTING ITEM QUEUING PROCESS ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items' => count($items ?? [])
        ]);

        if (empty($items)) {
            Log::warning("No items found in invoice for queuing", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            return 0;
        }

        foreach ($items as $index => $item) {
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            
            Log::info("Processing item " . ($index + 1), [
                'item_id' => $itemId,
                'item_name' => $item['name'] ?? 'Unknown',
                'quantity' => $item['quantity'] ?? 1
            ]);

            if (!$itemId) {
                Log::warning("Item ID not found, skipping", ['item_data' => $item]);
                continue;
            }

            // Get the item from database
            $itemModel = Item::find($itemId);
            if (!$itemModel) {
                Log::warning("Item model not found in database", ['item_id' => $itemId]);
                continue;
            }

            $quantity = $item['quantity'] ?? 1;

            // Get service point through BranchServicePoint relationship
            $branchServicePoint = $itemModel->branchServicePoints()
                ->where('business_id', $invoice->business_id)
                ->where('branch_id', $invoice->branch_id)
                ->first();

            Log::info("Found item model", [
                'item_id' => $itemModel->id,
                'item_name' => $itemModel->name,
                'item_type' => $itemModel->type,
                'business_id' => $invoice->business_id,
                'branch_id' => $invoice->branch_id,
                'branch_service_point_found' => $branchServicePoint ? 'yes' : 'no',
                'service_point_id' => $branchServicePoint ? $branchServicePoint->service_point_id : null
            ]);

            // Handle regular items with service points
            if ($branchServicePoint && $branchServicePoint->service_point_id) {
                Log::info("Creating service delivery queue for regular item", [
                    'item_id' => $itemId,
                    'service_point_id' => $branchServicePoint->service_point_id,
                    'quantity' => $quantity
                ]);

                $queueRecord = \App\Models\ServiceDeliveryQueue::create([
                    'business_id' => $invoice->business_id,
                    'branch_id' => $invoice->branch_id,
                    'service_point_id' => $branchServicePoint->service_point_id,
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'item_id' => $itemId,
                    'item_name' => $item['name'] ?? $itemModel->name,
                    'quantity' => $quantity,
                    'price' => $item['price'] ?? $itemModel->default_price ?? 0,
                    'status' => 'pending',
                    'priority' => 'normal',
                    'notes' => "Invoice: {$invoice->invoice_number}, Client: {$invoice->client_name}",
                    'queued_at' => now(),
                    'estimated_delivery_time' => now()->addHours(2), // Default 2 hours
                ]);

                $queuedCount++;
                Log::info("Service delivery queue created for regular item", [
                    'queue_id' => $queueRecord->id,
                    'item_id' => $itemId,
                    'service_point_id' => $branchServicePoint->service_point_id
                ]);
            } else {
                Log::info("Regular item has no service point for this business/branch, skipping queuing", [
                    'item_id' => $itemId,
                    'item_name' => $itemModel->name,
                    'business_id' => $invoice->business_id,
                    'branch_id' => $invoice->branch_id,
                    'branch_service_point_found' => $branchServicePoint ? 'yes' : 'no'
                ]);
            }

            // Handle bulk items - queue each included item at its respective service point
            if ($itemModel->type === 'bulk') {
                Log::info("Processing bulk item", [
                    'bulk_item_id' => $itemId,
                    'bulk_name' => $itemModel->name
                ]);

                $bulkItems = $itemModel->bulkItems()->with('includedItem')->get();
                
                Log::info("Found bulk items", [
                    'bulk_item_id' => $itemId,
                    'included_items_count' => $bulkItems->count()
                ]);
                
                foreach ($bulkItems as $bulkItemIndex => $bulkItem) {
                    $includedItem = $bulkItem->includedItem;
                    $fixedQuantity = $bulkItem->fixed_quantity ?? 1;
                    $totalQuantity = $fixedQuantity * $quantity;

                    // Get service point for included item through BranchServicePoint relationship
                    $includedItemBranchServicePoint = $includedItem->branchServicePoints()
                        ->where('business_id', $invoice->business_id)
                        ->where('branch_id', $invoice->branch_id)
                        ->first();

                    Log::info("Processing included item " . ($bulkItemIndex + 1), [
                        'included_item_id' => $includedItem->id,
                        'included_item_name' => $includedItem->name,
                        'fixed_quantity' => $fixedQuantity,
                        'total_quantity' => $totalQuantity,
                        'business_id' => $invoice->business_id,
                        'branch_id' => $invoice->branch_id,
                        'branch_service_point_found' => $includedItemBranchServicePoint ? 'yes' : 'no',
                        'service_point_id' => $includedItemBranchServicePoint ? $includedItemBranchServicePoint->service_point_id : null
                    ]);

                    // Only queue if the included item has a service point for this business/branch
                    if ($includedItemBranchServicePoint && $includedItemBranchServicePoint->service_point_id) {
                        Log::info("Creating service delivery queue for included item", [
                            'included_item_id' => $includedItem->id,
                            'service_point_id' => $includedItemBranchServicePoint->service_point_id,
                            'total_quantity' => $totalQuantity
                        ]);

                        $queueRecord = \App\Models\ServiceDeliveryQueue::create([
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'service_point_id' => $includedItemBranchServicePoint->service_point_id,
                            'invoice_id' => $invoice->id,
                            'client_id' => $invoice->client_id,
                            'item_id' => $includedItem->id,
                            'item_name' => $includedItem->name,
                            'quantity' => $totalQuantity,
                            'price' => $includedItem->default_price ?? 0,
                            'status' => 'pending',
                            'priority' => 'normal',
                            'notes' => "Bulk: {$itemModel->name}, Invoice: {$invoice->invoice_number}, Client: {$invoice->client_name}",
                            'queued_at' => now(),
                            'estimated_delivery_time' => now()->addHours(2), // Default 2 hours
                        ]);

                        $queuedCount++;
                        Log::info("Service delivery queue created for included item", [
                            'queue_id' => $queueRecord->id,
                            'included_item_id' => $includedItem->id,
                            'service_point_id' => $includedItemBranchServicePoint->service_point_id
                        ]);
                    } else {
                        Log::info("Included item has no service point for this business/branch, skipping queuing", [
                            'included_item_id' => $includedItem->id,
                            'included_item_name' => $includedItem->name,
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'branch_service_point_found' => $includedItemBranchServicePoint ? 'yes' : 'no'
                        ]);
                    }
                }
            }

            // Handle package items - packages use their own tracking system, not service point queuing
            if ($itemModel->type === 'package') {
                Log::info("Package item detected - using package tracking system instead of service point queuing", [
                    'package_item_id' => $itemId,
                    'package_name' => $itemModel->name,
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id
                ]);
                
                // Packages are handled by package tracking logic, not service point queuing
                // No queuing action needed here
            }
        }

        Log::info("=== ITEM QUEUING PROCESS COMPLETED ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items_processed' => count($items),
            'total_items_queued' => $queuedCount
        ]);

        return $queuedCount;
    }
}
