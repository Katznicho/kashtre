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
                        
                        // Create package tracking records FIRST so they exist when processing suspense movements
                        Log::info("Creating package tracking records (simulated)", [
                            'invoice_id' => $invoice->id,
                            'items_count' => count($invoice->items ?? [])
                        ]);
                        
                        $this->createPackageTrackingRecords($invoice, $invoice->items);
                        
                        Log::info("Package tracking records created (simulated)", [
                            'invoice_id' => $invoice->id
                        ]);
                        
                        // Move money from client suspense to appropriate suspense accounts after payment completion
                        Log::info("🔄 PROCESSING SUSPENSE ACCOUNT MONEY MOVEMENT AFTER SIMULATED PAYMENT COMPLETION", [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'items_count' => count($invoice->items ?? []),
                            'items_data' => $invoice->items
                        ]);
                        
                        try {
                            $suspenseMovements = $moneyTrackingService->processSuspenseAccountMovements($invoice, $invoice->items);
                            
                            Log::info("✅ SUSPENSE ACCOUNT MOVEMENTS COMPLETED (SIMULATED)", [
                                'invoice_id' => $invoice->id,
                                'suspense_movements_count' => count($suspenseMovements),
                                'suspense_movements' => $suspenseMovements
                            ]);
                        } catch (\Exception $e) {
                            Log::error("❌ SUSPENSE ACCOUNT MOVEMENTS FAILED (SIMULATED)", [
                                'invoice_id' => $invoice->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                        
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

                Log::info("🎉 === SIMULATED PAYMENT SUCCEEDED - Transaction processing completed ===", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'business_id' => $transaction->business_id,
                    'invoice_id' => $transaction->invoice_id,
                    'invoice_number' => $transaction->invoice ? $transaction->invoice->invoice_number : 'N/A'
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
        
        Log::info("=== STARTING ITEM QUEUING PROCESS (SIMULATED) ===", [
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
            $itemModel = \App\Models\Item::find($itemId);
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
                'item_id' => $itemId,
                'item_name' => $itemModel->name,
                'item_type' => $itemModel->type,
                'has_branch_service_point' => $branchServicePoint ? true : false,
                'service_point_id' => $branchServicePoint->service_point_id ?? null
            ]);

            // Handle package items FIRST - packages use their own tracking system, not service point queuing
            if ($itemModel->type === 'package') {
                Log::info("Package item detected - using package tracking system instead of service point queuing", [
                    'package_item_id' => $itemId,
                    'package_name' => $itemModel->name,
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id
                ]);
                
                // Packages are handled by package tracking logic, not service point queuing
                // No queuing action needed here
                continue; // Skip to next item
            }

            // Handle bulk items - bulk items don't have service points, use service point from one of their included items
            if ($itemModel->type === 'bulk') {
                Log::info("Processing bulk item", [
                    'bulk_item_id' => $itemId,
                    'bulk_name' => $itemModel->name
                ]);

                // Get service point from one of the bulk item's contained items
                Log::info("Bulk items don't have service points, checking contained items for service point", [
                    'bulk_item_id' => $itemId,
                    'bulk_name' => $itemModel->name
                ]);

                $bulkItems = $itemModel->bulkItems()->with('includedItem')->get();
                
                Log::info("Found bulk items for service point lookup", [
                    'bulk_item_id' => $itemId,
                    'included_items_count' => $bulkItems->count()
                ]);

                $servicePointId = null;
                $selectedIncludedItem = null;
                
                // Find the first included item that has a service point for this business/branch
                foreach ($bulkItems as $bulkItem) {
                    $includedItem = $bulkItem->includedItem;
                    
                    $includedItemBranchServicePoint = $includedItem->branchServicePoints()
                        ->where('business_id', $invoice->business_id)
                        ->where('branch_id', $invoice->branch_id)
                        ->first();

                    if ($includedItemBranchServicePoint && $includedItemBranchServicePoint->service_point_id) {
                        $servicePointId = $includedItemBranchServicePoint->service_point_id;
                        $selectedIncludedItem = $includedItem;
                        Log::info("Found service point from included item", [
                            'included_item_id' => $includedItem->id,
                            'included_item_name' => $includedItem->name,
                            'service_point_id' => $servicePointId
                        ]);
                        break;
                    }
                }

                // Queue bulk item at the service point from its included item
                if ($servicePointId) {
                    Log::info("Creating service delivery queue for bulk item using included item's service point", [
                        'bulk_item_id' => $itemId,
                        'service_point_id' => $servicePointId,
                        'selected_included_item' => $selectedIncludedItem ? $selectedIncludedItem->name : 'Unknown',
                        'quantity' => $quantity
                    ]);

                    $queueRecord = \App\Models\ServiceDeliveryQueue::create([
                        'business_id' => $invoice->business_id,
                        'branch_id' => $invoice->branch_id,
                        'service_point_id' => $servicePointId,
                        'invoice_id' => $invoice->id,
                        'client_id' => $invoice->client_id,
                        'item_id' => $itemId,
                        'item_name' => $item['name'] ?? $itemModel->name,
                        'quantity' => $quantity,
                        'price' => $item['price'] ?? $itemModel->default_price ?? 0,
                        'status' => 'pending',
                        'priority' => 'normal',
                        'notes' => "Bulk item queued at SP from included item: {$selectedIncludedItem->name}, Invoice: {$invoice->invoice_number}, Client: {$invoice->client_name}",
                        'queued_at' => now(),
                        'estimated_delivery_time' => now()->addHours(2), // Default 2 hours
                    ]);

                    $queuedCount++;
                    Log::info("Service delivery queue created for bulk item using included item's service point", [
                        'queue_id' => $queueRecord->id,
                        'bulk_item_id' => $itemId,
                        'service_point_id' => $servicePointId,
                        'selected_included_item' => $selectedIncludedItem ? $selectedIncludedItem->name : 'Unknown'
                    ]);
                } else {
                    Log::info("No service point found for bulk item or any of its contained items, skipping queuing", [
                        'bulk_item_id' => $itemId,
                        'bulk_name' => $itemModel->name,
                        'business_id' => $invoice->business_id,
                        'branch_id' => $invoice->branch_id
                    ]);
                }
            }

            // Handle regular items with service points (only for non-package, non-bulk items)
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
        }

        Log::info("=== ITEM QUEUING PROCESS COMPLETED (SIMULATED) ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items_processed' => count($items),
            'total_items_queued' => $queuedCount
        ]);

        return $queuedCount;
    }

    private function createPackageTrackingRecords($invoice, $items)
    {
        $packageTrackingService = new \App\Services\PackageTrackingService();
        $packageTrackingCount = 0;
        
        Log::info("=== STARTING PACKAGE TRACKING CREATION (SIMULATED) ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items' => count($items ?? [])
        ]);

        if (empty($items)) {
            Log::warning("No items found for package tracking", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            return;
        }

        foreach ($items as $index => $item) {
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            
            Log::info("🔍 PROCESSING ITEM " . ($index + 1) . " FOR PACKAGE TRACKING (SIMULATED)", [
                'item_id' => $itemId,
                'item_data' => $item,
                'item_name' => $item['name'] ?? 'Unknown'
            ]);
            
            if (!$itemId) {
                Log::warning("❌ No item ID found for item " . ($index + 1), [
                    'item_data' => $item
                ]);
                continue;
            }

            // Get the item from database to check if it's a package
            $itemModel = \App\Models\Item::find($itemId);
            if (!$itemModel) {
                Log::warning("❌ Item model not found", [
                    'item_id' => $itemId,
                    'item_name' => $item['name'] ?? 'Unknown'
                ]);
                continue;
            }
            
            Log::info("📦 ITEM MODEL DETAILS (SIMULATED)", [
                'item_id' => $itemModel->id,
                'item_name' => $itemModel->name,
                'item_type' => $itemModel->type,
                'is_package' => $itemModel->type === 'package'
            ]);
            
            if ($itemModel->type !== 'package') {
                Log::info("⏭️ SKIPPING NON-PACKAGE ITEM (SIMULATED)", [
                    'item_id' => $itemModel->id,
                    'item_name' => $itemModel->name,
                    'item_type' => $itemModel->type,
                    'reason' => 'Item is not a package type'
                ]);
                continue;
            }

            $quantity = $item['quantity'] ?? 1;

            // Check if package tracking record already exists to prevent duplicates
            $existingTracking = \App\Models\PackageTracking::where([
                'invoice_id' => $invoice->id,
                'package_item_id' => $itemId,
                'client_id' => $invoice->client_id
            ])->first();

            if ($existingTracking) {
                Log::warning("Package tracking record already exists - skipping creation to prevent duplicate", [
                    'existing_tracking_id' => $existingTracking->id,
                    'invoice_id' => $invoice->id,
                    'package_item_id' => $itemId,
                    'client_id' => $invoice->client_id
                ]);
                continue;
            }

            try {
                // Use the new service to create package tracking
                $packageTracking = $packageTrackingService->createPackageTracking($invoice, $item, $quantity);
                $packageTrackingCount++;
                
                Log::info("Package tracking created successfully (SIMULATED)", [
                    'package_tracking_id' => $packageTracking->id,
                    'tracking_number' => $packageTracking->tracking_number,
                    'package_item_id' => $itemId,
                    'quantity' => $quantity
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to create package tracking (SIMULATED)", [
                    'error' => $e->getMessage(),
                    'invoice_id' => $invoice->id,
                    'package_item_id' => $itemId,
                    'quantity' => $quantity
                ]);
                continue;
            }
        }

        Log::info("=== PACKAGE TRACKING CREATION COMPLETED (SIMULATED) ===", [
            'invoice_id' => $invoice->id,
            'package_tracking_count' => $packageTrackingCount
        ]);
    }
}
