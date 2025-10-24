<?php

namespace App\Http\Controllers;

// Package functionality with comprehensive logging - Updated for testing
// Version: 2025-09-20-20:30 - Fresh deployment with package transaction type support

use App\Models\Invoice;
use App\Models\ServiceCharge;
use App\Models\Client;
use App\Services\MoneyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $currentInvoice;
    /**
     * Calculate package adjustment for a client
     */
    public function calculatePackageAdjustment(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'business_id' => 'required|exists:businesses,id',
                'items' => 'required|array',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $clientId = $validated['client_id'];
            $businessId = $validated['business_id'];
            $branchId = $validated['branch_id'];
            $items = $validated['items'];
            
            Log::info("=== PACKAGE ADJUSTMENT CALCULATION STARTED ===", [
                'client_id' => $clientId,
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'items_count' => count($items),
                'items' => $items
            ]);

            // Use the new PackageTrackingService for package adjustment calculation
            $packageTrackingService = new \App\Services\PackageTrackingService();
            
            // Create a mock invoice object for the service
            $mockInvoice = new \App\Models\Invoice();
            $mockInvoice->client_id = $clientId;
            $mockInvoice->business_id = $businessId;
            
               $result = $packageTrackingService->calculatePackageAdjustment($mockInvoice, $items);
            $totalAdjustment = $result['total_adjustment'];
            $adjustmentDetails = $result['details'];

            // Package adjustment calculation is now handled by the PackageTrackingService

            Log::info("=== PACKAGE ADJUSTMENT CALCULATION COMPLETED ===", [
                'client_id' => $clientId,
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'total_adjustment' => $totalAdjustment,
                'adjustment_details_count' => count($adjustmentDetails),
                'adjustment_details' => $adjustmentDetails
            ]);

            return response()->json([
                'success' => true,
                'total_adjustment' => $totalAdjustment,
                'details' => $adjustmentDetails,
                'message' => $totalAdjustment > 0 ? 'Package adjustments applied' : 'No valid package adjustments found'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating package adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update package tracking records when package adjustments are used
     */
    private function updatePackageTrackingForAdjustments($invoice, $items)
    {
        try {
            $clientId = $invoice->client_id;
            $businessId = $invoice->business_id;
            $branchId = $invoice->branch_id;
            
            Log::info("=== PACKAGE TRACKING UPDATE STARTED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $clientId,
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'package_adjustment' => $invoice->package_adjustment,
                'items_count' => count($items)
            ]);
            
            // Get client's valid package tracking records
            $validPackages = \App\Models\PackageTracking::where('client_id', $clientId)
                ->where('business_id', $businessId)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->where('valid_until', '>=', now()->toDateString())
                ->with(['packageItem.packageItems.includedItem'])
                ->get();
                
            Log::info("Found valid packages for client", [
                'client_id' => $clientId,
                'valid_packages_count' => $validPackages->count(),
                'package_details' => $validPackages->map(function($pkg) {
                    return [
                        'id' => $pkg->id,
                        'package_name' => $pkg->packageItem->name,
                        'remaining_quantity' => $pkg->remaining_quantity,
                        'used_quantity' => $pkg->used_quantity,
                        'status' => $pkg->status,
                        'valid_until' => $pkg->valid_until
                    ];
                })
            ]);

            foreach ($items as $item) {
                $itemId = $item['id'] ?? $item['item_id'];
                $quantity = $item['quantity'] ?? 1;
                $remainingQuantity = $quantity;
                
                Log::info("Processing item for package tracking update", [
                    'item_id' => $itemId,
                    'item_name' => $item['name'] ?? 'Unknown',
                    'quantity' => $quantity,
                    'remaining_quantity' => $remainingQuantity
                ]);
                
                // Get the item price (branch-specific or default)
                $itemModel = \App\Models\Item::find($itemId);
                $price = $itemModel ? $itemModel->default_price : 0;
                
                // Check for branch-specific price
                $branchPrice = \App\Models\BranchItemPrice::where('branch_id', $branchId)
                    ->where('item_id', $itemId)
                    ->first();
                
                if ($branchPrice) {
                    $price = $branchPrice->price;
                    Log::info("Using branch-specific price for item", [
                        'item_id' => $itemId,
                        'branch_id' => $branchId,
                        'branch_price' => $price,
                        'default_price' => $itemModel->default_price
                    ]);
                } else {
                    Log::info("Using default price for item", [
                        'item_id' => $itemId,
                        'default_price' => $price
                    ]);
                }

                // Check if this item is included in any valid packages
                foreach ($validPackages as $packageTracking) {
                    if ($remainingQuantity <= 0) break;

                    Log::info("Checking package for item match", [
                        'package_tracking_id' => $packageTracking->id,
                        'package_name' => $packageTracking->packageItem->name,
                        'item_id' => $itemId,
                        'package_remaining_quantity' => $packageTracking->remaining_quantity
                    ]);

                    // Check if the current item is included in this package
                    $packageItems = $packageTracking->packageItem->packageItems;
                    
                    foreach ($packageItems as $packageItem) {
                        if ($packageItem->included_item_id == $itemId) {
                            Log::info("Item found in package", [
                                'package_tracking_id' => $packageTracking->id,
                                'package_item_id' => $packageItem->id,
                                'included_item_id' => $packageItem->included_item_id,
                                'max_quantity' => $packageItem->max_quantity,
                                'fixed_quantity' => $packageItem->fixed_quantity
                            ]);
                            
                            // Check max quantity constraint from package_items table
                            $maxQuantity = $packageItem->max_quantity ?? null;
                            $fixedQuantity = $packageItem->fixed_quantity ?? null;
                            
                            // Determine how much quantity can be used from this package
                            $availableFromPackage = $packageTracking->remaining_quantity;
                            
                            if ($maxQuantity !== null) {
                                // If max_quantity is set, limit by that
                                $availableFromPackage = min($availableFromPackage, $maxQuantity);
                                Log::info("Limited by max_quantity constraint", [
                                    'max_quantity' => $maxQuantity,
                                    'available_from_package' => $availableFromPackage
                                ]);
                            } elseif ($fixedQuantity !== null) {
                                // If fixed_quantity is set, use that
                                $availableFromPackage = min($availableFromPackage, $fixedQuantity);
                                Log::info("Limited by fixed_quantity constraint", [
                                    'fixed_quantity' => $fixedQuantity,
                                    'available_from_package' => $availableFromPackage
                                ]);
                            }
                            
                            // Calculate how much we can actually use
                            $quantityToUse = min($remainingQuantity, $availableFromPackage);
                            
                            Log::info("Calculated quantity to use from package", [
                                'remaining_quantity_needed' => $remainingQuantity,
                                'available_from_package' => $availableFromPackage,
                                'quantity_to_use' => $quantityToUse
                            ]);
                            
                            if ($quantityToUse > 0) {
                                // Store old values for logging
                                $oldUsedQuantity = $packageTracking->used_quantity;
                                $oldRemainingQuantity = $packageTracking->remaining_quantity;
                                $oldStatus = $packageTracking->status;
                                
                                // Update package tracking record
                                $packageTracking->used_quantity += $quantityToUse;
                                $packageTracking->remaining_quantity -= $quantityToUse;
                                
                                // Mark as expired if no remaining quantity
                                if ($packageTracking->remaining_quantity <= 0) {
                                    $packageTracking->status = 'expired';
                                }
                                
                                $packageTracking->save();
                                
                                Log::info("Successfully updated package tracking for adjustment", [
                                    'package_tracking_id' => $packageTracking->id,
                                    'package_name' => $packageTracking->packageItem->name,
                                    'item_name' => $item['name'] ?? 'Unknown',
                                    'quantity_used' => $quantityToUse,
                                    'old_used_quantity' => $oldUsedQuantity,
                                    'new_used_quantity' => $packageTracking->used_quantity,
                                    'old_remaining_quantity' => $oldRemainingQuantity,
                                    'new_remaining_quantity' => $packageTracking->remaining_quantity,
                                    'old_status' => $oldStatus,
                                    'new_status' => $packageTracking->status
                                ]);
                                
                                $remainingQuantity -= $quantityToUse;
                                
                                // If we've used all available quantity from this package, break
                                if ($remainingQuantity <= 0) break;
                            } else {
                                Log::info("No quantity to use from package", [
                                    'package_tracking_id' => $packageTracking->id,
                                    'reason' => 'quantity_to_use is 0'
                                ]);
                            }
                        }
                    }
                }
                
                Log::info("Finished processing item for package tracking", [
                    'item_id' => $itemId,
                    'original_quantity' => $quantity,
                    'remaining_quantity_after_package_usage' => $remainingQuantity
                ]);
            }
            
            Log::info("=== PACKAGE TRACKING UPDATE COMPLETED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_items_processed' => count($items)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error updating package tracking for adjustments", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Store a new invoice
     */
    public function store(Request $request)
    {
        try {
            Log::info("=== INVOICE CREATION STARTED ===", [
                'user_id' => Auth::id(),
                'business_id' => $request->input('business_id'),
                'client_id' => $request->input('client_id'),
                'total_amount' => $request->input('total_amount'),
                'payment_status' => $request->input('payment_status'),
                'status' => $request->input('status')
            ]);

            DB::beginTransaction();
            
            $user = Auth::user();
            $business = $user->business;
            $moneyTrackingService = new MoneyTrackingService();
            
            // Validate request
            $validated = $request->validate([
                'invoice_number' => 'nullable|string|unique:invoices,invoice_number',
                'client_id' => 'required|exists:clients,id',
                'business_id' => 'required|exists:businesses,id',
                'branch_id' => 'required|exists:branches,id',
                'created_by' => 'required|exists:users,id',
                'client_name' => 'required|string',
                'client_phone' => 'required|string',
                'payment_phone' => 'nullable|string',
                'visit_id' => 'required|string',
                'items' => 'required|array',
                'subtotal' => 'required|numeric|min:0',
                'package_adjustment' => 'nullable|numeric',
                'account_balance_adjustment' => 'nullable|numeric',
                'service_charge' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'balance_due' => 'required|numeric|min:0',
                'payment_methods' => 'nullable|array',
                'payment_status' => 'required|in:pending,partial,paid,cancelled',
                'status' => 'required|in:draft,confirmed,printed,cancelled',
                'notes' => 'nullable|string',
            ]);
            
            // Get client
            $client = Client::find($validated['client_id']);
            
            // Validate service charge for non-package invoices
            if ($validated['package_adjustment'] <= 0) {
                // For non-package invoices, ensure service charge is applied
                if ($validated['service_charge'] <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service charge not configured. Please contact support.',
                        'errors' => ['service_charge' => ['Service charge not configured. Please contact support.']]
                    ], 422);
                }
            }
            
            // Use provided invoice number or generate one
            // Always start as proforma invoice (P prefix)
            $invoiceNumber = $validated['invoice_number'] ?? Invoice::generateInvoiceNumber($business->id, 'proforma');
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'client_id' => $validated['client_id'],
                'business_id' => $validated['business_id'],
                'branch_id' => $validated['branch_id'],
                'created_by' => $validated['created_by'],
                'client_name' => $validated['client_name'],
                'client_phone' => $validated['client_phone'],
                'payment_phone' => $validated['payment_phone'],
                'visit_id' => $validated['visit_id'],
                'items' => $validated['items'],
                'subtotal' => $validated['subtotal'],
                'package_adjustment' => $validated['package_adjustment'] ?? 0,
                'account_balance_adjustment' => $validated['account_balance_adjustment'] ?? 0,
                'service_charge' => $validated['service_charge'],
                'total_amount' => $validated['total_amount'],
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'balance_due' => $validated['balance_due'],
                'payment_methods' => $validated['payment_methods'] ?? [],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? '',
                'confirmed_at' => $validated['status'] === 'confirmed' ? now() : null,
            ]);

            Log::info("Invoice created successfully", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'business_id' => $invoice->business_id,
                'total_amount' => $invoice->total_amount,
                'payment_status' => $invoice->payment_status,
                'status' => $invoice->status,
                'items_count' => count($invoice->items ?? [])
            ]);
            
            // Store current invoice for balance statement
            $this->currentInvoice = $invoice;
            
            // MONEY TRACKING: Step 1 - Process payment received
            // For mobile money payments, this will be handled by the cron job when payment is completed
            // For cash payments, process immediately
            if ($validated['amount_paid'] > 0) {
                $paymentMethods = $validated['payment_methods'] ?? [];
                $primaryMethod = !empty($paymentMethods) ? $paymentMethods[0] : 'cash';
                
                // Only process payment immediately for cash payments
                // Mobile money payments will be processed by the cron job when payment is completed
                if ($primaryMethod === 'cash') {
                    Log::info("Processing cash payment immediately", [
                        'client_id' => $client->id,
                        'amount_paid' => $validated['amount_paid'],
                        'invoice_number' => $invoiceNumber,
                        'primary_method' => $primaryMethod
                    ]);
                    
                    // Process payment through money tracking system
                    $moneyTrackingService->processPaymentReceived(
                        $client,
                        $validated['amount_paid'],
                        $invoiceNumber,
                        $primaryMethod,
                        [
                            'invoice_id' => $invoice->id,
                            'payment_methods' => $paymentMethods,
                            'payment_phone' => $validated['payment_phone']
                        ]
                    );
                    
                    Log::info("Cash payment processed successfully", [
                        'client_id' => $client->id,
                        'amount_paid' => $validated['amount_paid']
                    ]);
                } else {
                    Log::info("Mobile money payment detected - will be processed by cron job when payment is completed", [
                        'client_id' => $client->id,
                        'amount_paid' => $validated['amount_paid'],
                        'invoice_number' => $invoiceNumber,
                        'primary_method' => $primaryMethod
                    ]);
                    
                    // Update any existing mobile money transaction with the invoice_id
                    $existingMobileMoneyTransaction = \App\Models\Transaction::where('reference', $invoiceNumber)
                        ->where('client_id', $validated['client_id'])
                        ->where('amount', $validated['amount_paid'])
                        ->where('method', 'mobile_money')
                        ->whereNull('invoice_id')
                        ->first();
                    
                    if ($existingMobileMoneyTransaction) {
                        $existingMobileMoneyTransaction->update(['invoice_id' => $invoice->id]);
                        Log::info("Updated mobile money transaction with invoice_id", [
                            'transaction_id' => $existingMobileMoneyTransaction->id,
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoiceNumber
                        ]);
                    }
                }
                
                // Only create transaction record for cash payments
                // Mobile money transactions are created in processMobileMoneyPayment method
                if ($primaryMethod === 'cash') {
                    // Check if a transaction already exists for this invoice (to prevent duplicates)
                    $existingTransaction = \App\Models\Transaction::where('reference', $invoiceNumber)
                        ->where('client_id', $validated['client_id'])
                        ->where('amount', $validated['amount_paid'])
                        ->first();
                    
                    // Only create transaction record if one doesn't already exist
                    if (!$existingTransaction) {
                        // Build description with purchased items, client, and business information
                        $itemsDescription = $this->buildItemsDescription($validated['items'], $client, $business, $invoiceNumber);
                        
                        \App\Models\Transaction::create([
                            'business_id' => $validated['business_id'],
                            'branch_id' => $validated['branch_id'],
                            'amount' => $validated['amount_paid'],
                            'reference' => $invoiceNumber,
                            'description' => $itemsDescription,
                            'status' => 'completed',
                            'type' => 'debit',
                            'origin' => 'web',
                            'phone_number' => $validated['payment_phone'] ?? $validated['client_phone'],
                            'provider' => 'cash',
                            'service' => 'invoice_payment',
                            'date' => now(),
                            'currency' => 'UGX',
                            'names' => $validated['client_name'],
                            'email' => null,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'method' => $primaryMethod,
                            'transaction_for' => 'main',
                        ]);
                    } else {
                        // Update existing transaction with invoice_id if it doesn't have one
                        if (!$existingTransaction->invoice_id) {
                            $existingTransaction->update(['invoice_id' => $invoice->id]);
                        }
                    }
                }
            }
            
            // MONEY TRACKING: Step 2 - Process order confirmation (includes service charge)
            if ($validated['status'] === 'confirmed') {
                Log::info("Processing order confirmation", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'items_count' => count($validated['items'])
                ]);

                $items = $validated['items'];
                $orderConfirmationResult = $moneyTrackingService->processOrderConfirmed($invoice, $items);
                
                Log::info("Order confirmation processed", [
                    'invoice_id' => $invoice->id,
                    'result' => $orderConfirmationResult
                ]);
            }
            
        // PACKAGE TRACKING: Package tracking records will be created after payment completion
        // (handled in CheckPaymentStatus.php when payment is confirmed)
            
            // PACKAGE ADJUSTMENT: Log that package adjustments will be processed after payment completion
            if ($validated['package_adjustment'] > 0) {
                Log::info("=== PACKAGE ADJUSTMENT DETECTED IN INVOICE CREATION ===", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'package_adjustment_amount' => $validated['package_adjustment'],
                    'subtotal' => $validated['subtotal'],
                    'subtotal_1' => $validated['subtotal_1'] ?? 0,
                    'subtotal_2' => $validated['subtotal_2'] ?? 0,
                    'service_charge' => $validated['service_charge'] ?? 0,
                    'total_amount' => $validated['total_amount'],
                    'items_count' => count($validated['items']),
                    'items_details' => $validated['items'],
                    'client_id' => $validated['client_id'],
                    'client_name' => $client->name ?? 'Unknown',
                    'business_id' => $validated['business_id'],
                    'business_name' => $business->name ?? 'Unknown',
                    'branch_id' => $validated['branch_id'],
                    'timestamp' => now()->toISOString(),
                    'note' => 'Package tracking will be updated after payment completion via Save & Exit'
                ]);
            }
            
            // BALANCE ADJUSTMENT: Update client balance if balance adjustment was used
            if ($validated['account_balance_adjustment'] > 0) {
                $this->updateClientBalance($client, $validated['account_balance_adjustment']);
            }
            
            // SERVICE POINT QUEUING: Items will be queued only after payment is completed
            // This is now handled by the CheckPaymentStatus cron job
            // EXCEPTION: For zero-amount transactions (due to package adjustments OR balance adjustments), queue immediately
            // BUT ONLY if it's not a mobile money transaction (which should remain pending until payment confirmation)
            if ($validated['total_amount'] == 0 && !in_array('mobile_money', $validated['payment_methods'] ?? [])) {
                Log::info("Zero-amount transaction detected - auto-completing and queuing items", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $validated['total_amount'],
                    'package_adjustment' => $validated['package_adjustment'] ?? 0,
                    'account_balance_adjustment' => $validated['account_balance_adjustment'] ?? 0,
                    'payment_methods' => $validated['payment_methods'] ?? []
                ]);
                
                // Auto-complete the transaction
                $invoice->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'amount_paid' => 0,
                    'balance_due' => 0,
                    'confirmed_at' => now()
                ]);
                
                // Create transaction record for zero-amount transactions
                $itemsDescription = $this->buildItemsDescription($validated['items'], $client, $business, $invoiceNumber);
                
                \App\Models\Transaction::create([
                    'business_id' => $validated['business_id'],
                    'branch_id' => $validated['branch_id'],
                    'client_id' => $validated['client_id'],
                    'invoice_id' => $invoice->id,
                    'amount' => 0,
                    'reference' => $invoiceNumber,
                    'description' => $itemsDescription,
                    'status' => 'completed',
                    'type' => 'debit',
                    'origin' => 'web',
                    'phone_number' => $validated['payment_phone'] ?? $validated['client_phone'],
                    'provider' => 'yo', // Use 'yo' as default provider for zero-amount transactions
                    'service' => 'invoice_payment',
                    'date' => now(),
                    'currency' => 'UGX',
                    'names' => $validated['client_name'],
                    'email' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'method' => 'package_adjustment',
                    'transaction_for' => 'main',
                ]);
                
                Log::info("Transaction record created for zero-amount transaction", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => 0,
                    'description' => $itemsDescription
                ]);
                
                // Queue items immediately for zero-amount transactions
                $this->queueItemsAtServicePoints($invoice, $validated['items']);
                
                Log::info("Zero-amount transaction auto-completed and items queued", [
                    'invoice_id' => $invoice->id,
                    'items_queued' => count($validated['items'])
                ]);
            } else {
                // For mobile money transactions with zero amount, keep them pending until payment confirmation
                Log::info("Mobile money transaction with zero amount - keeping pending until payment confirmation", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $validated['total_amount'],
                    'payment_methods' => $validated['payment_methods'] ?? [],
                    'current_status' => $invoice->status,
                    'current_payment_status' => $invoice->payment_status
                ]);
                
                // Ensure invoice remains in pending status for mobile money transactions
                $invoice->update([
                    'payment_status' => 'pending',
                    'status' => 'draft' // Keep as draft until payment is confirmed
                ]);
            }
            
            // Generate next invoice number (default to proforma type)
            $nextInvoiceNumber = Invoice::generateInvoiceNumber($business->id, 'proforma');
            
            DB::commit();
            
            Log::info("=== INVOICE CREATION COMPLETED SUCCESSFULLY ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'business_id' => $invoice->business_id,
                'total_amount' => $invoice->total_amount,
                'payment_status' => $invoice->payment_status,
                'status' => $invoice->status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice,
                'next_invoice_number' => $nextInvoiceNumber,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Build description with purchased items, client, and business information
     * Simplified to avoid XML special characters that cause parse errors
     */
    private function buildItemsDescription($items, $client = null, $business = null, $invoiceNumber = null)
    {
        // Keep it simple: Proforma Invoice {number}
        if ($invoiceNumber) {
            return "Proforma Invoice {$invoiceNumber}";
        }
        
        // If no invoice number, use client name
        if ($client) {
            return $client->name;
        }
        
        // Fallback
        return 'Services';
    }

    /**
     * Calculate service charge for a business
     */
    public function calculateServiceCharge($businessId, $subtotal)
    {
        // Get service charge settings for the business based on amount range
        // Order by lower_bound DESC to get the highest applicable range
        $serviceCharge = ServiceCharge::where('business_id', $businessId)
            ->where('entity_type', 'business')
            ->where('is_active', true)
            ->where('lower_bound', '<=', $subtotal)
            ->where('upper_bound', '>=', $subtotal)
            ->orderBy('lower_bound', 'desc')
            ->first();
        
        if (!$serviceCharge) {
            return 0; // No service charge configured for this amount range
        }
        
        // For fixed charges, return the amount directly
        if ($serviceCharge->type === 'fixed') {
            return $serviceCharge->amount;
        }
        
        // For percentage charges, calculate based on amount
        if ($serviceCharge->type === 'percentage') {
            return ($subtotal * $serviceCharge->amount) / 100;
        }
        
        return 0;
    }
    
    /**
     * Get service charge for AJAX request
     */
    public function getServiceCharge(Request $request)
    {
        $businessId = $request->input('business_id');
        $subtotal = $request->input('subtotal', 0);
        
        $serviceCharge = $this->calculateServiceCharge($businessId, $subtotal);
        
        return response()->json([
            'service_charge' => $serviceCharge,
            'formatted_service_charge' => 'UGX ' . number_format($serviceCharge, 2),
        ]);
    }
    
    /**
     * Calculate service charge for AJAX request (new endpoint)
     */
    public function serviceCharge(Request $request)
    {
        try {
            $validated = $request->validate([
                'subtotal' => 'required|numeric|min:0',
                'business_id' => 'required|exists:businesses,id',
                'branch_id' => 'nullable|exists:branches,id',
            ]);
            
            $businessId = $validated['business_id'];
            $subtotal = $validated['subtotal'];
            $branchId = $validated['branch_id'] ?? null;
            
            // Try to get service charge for branch first, then business
            $serviceCharge = null;
            
            // Debug: Check what service charges exist
            $allServiceCharges = ServiceCharge::where('business_id', $businessId)->get();
            \Log::info('All service charges for business', [
                'business_id' => $businessId,
                'count' => $allServiceCharges->count(),
                'charges' => $allServiceCharges->toArray()
            ]);
            
            if ($branchId) {
                $serviceCharge = ServiceCharge::where('business_id', $businessId)
                    ->where('entity_type', 'branch')
                    ->where('entity_id', $branchId)
                    ->where('is_active', true)
                    ->where('lower_bound', '<=', $subtotal)
                    ->where('upper_bound', '>=', $subtotal)
                    ->first();
                    
                \Log::info('Branch service charge search', [
                    'branch_id' => $branchId,
                    'subtotal' => $subtotal,
                    'found' => $serviceCharge ? 'yes' : 'no'
                ]);
            }
            
            // If no branch service charge, try business level
            if (!$serviceCharge) {
                $serviceCharge = ServiceCharge::where('business_id', $businessId)
                    ->where('entity_type', 'business')
                    ->where('is_active', true)
                    ->where('lower_bound', '<=', $subtotal)
                    ->where('upper_bound', '>=', $subtotal)
                    ->orderBy('lower_bound', 'desc')
                    ->first();
                    
                \Log::info('Business service charge search', [
                    'business_id' => $businessId,
                    'subtotal' => $subtotal,
                    'found' => $serviceCharge ? 'yes' : 'no'
                ]);
            }
            
            if (!$serviceCharge) {
                // Check if any service charge ranges exist for this business/branch
                $hasServiceChargeRanges = false;
                
                if ($branchId) {
                    // Check if branch has any service charge ranges
                    $hasServiceChargeRanges = ServiceCharge::where('business_id', $businessId)
                        ->where('entity_type', 'branch')
                        ->where('entity_id', $branchId)
                        ->where('is_active', true)
                        ->exists();
                }
                
                // If no branch ranges, check business level
                if (!$hasServiceChargeRanges) {
                    $hasServiceChargeRanges = ServiceCharge::where('business_id', $businessId)
                        ->where('entity_type', 'business')
                        ->where('is_active', true)
                        ->exists();
                }
                
                // Debug: Log what we're looking for
                \Log::info('No service charge found', [
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'subtotal' => $subtotal,
                    'searched_branch' => $branchId ? 'yes' : 'no',
                    'searched_business' => 'yes',
                    'has_service_charge_ranges' => $hasServiceChargeRanges
                ]);
                
                return response()->json([
                    'success' => true,
                    'service_charge' => 0,
                    'has_service_charge_ranges' => $hasServiceChargeRanges,
                    'message' => $hasServiceChargeRanges 
                        ? 'No service charge configured for this amount range. Please contact admin to set up service charges for this range.'
                        : 'No service charge configured. Please contact admin to set up service charges.',
                    'debug' => [
                        'business_id' => $businessId,
                        'branch_id' => $branchId,
                        'subtotal' => $subtotal,
                        'has_service_charge_ranges' => $hasServiceChargeRanges
                    ]
                ]);
            }
            
            // Calculate service charge based on type
            $calculatedCharge = 0;
            
            if ($serviceCharge->type === 'percentage') {
                $calculatedCharge = ($subtotal * $serviceCharge->amount) / 100;
            } else {
                // For fixed charges, return the amount directly
                $calculatedCharge = $serviceCharge->amount;
            }
            
            return response()->json([
                'success' => true,
                'service_charge' => $calculatedCharge,
                'has_service_charge_ranges' => true, // Service charge ranges are configured
                'service_charge_info' => [
                    'type' => $serviceCharge->type,
                    'amount' => $serviceCharge->amount,
                    'lower_bound' => $serviceCharge->lower_bound,
                    'upper_bound' => $serviceCharge->upper_bound,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating service charge: ' . $e->getMessage(),
                'service_charge' => 0,
            ], 500);
        }
    }
    
    /**
     * Calculate balance adjustment for a client
     */
    public function calculateBalanceAdjustment(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'total_amount' => 'required|numeric|min:0',
            ]);
            
            $client = Client::find($validated['client_id']);
            $availableBalance = $client->available_balance ?? 0;
            $totalBalance = $client->total_balance ?? 0;
            $suspenseBalance = $client->suspense_balance ?? 0;
            $totalAmount = $validated['total_amount'];
            
            // Calculate how much balance can be used (only from available balance)
            $balanceAdjustment = min($availableBalance, $totalAmount);
            
            return response()->json([
                'success' => true,
                'balance_adjustment' => $balanceAdjustment,
                'available_balance' => $availableBalance,
                'total_balance' => $totalBalance,
                'suspense_balance' => $suspenseBalance,
                'client_balance' => $availableBalance, // For backward compatibility
                'remaining_balance' => $availableBalance - $balanceAdjustment,
                'message' => $balanceAdjustment > 0 ? 'Balance adjustment applied' : 'No balance available for adjustment'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating balance adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(Request $request)
    {
        $businessId = $request->input('business_id');
        $invoiceNumber = Invoice::generateInvoiceNumber($businessId, 'proforma');
        
        return response()->json([
            'invoice_number' => $invoiceNumber,
        ]);
    }
    
    /**
     * Process mobile money payment with comprehensive description
     */
    public function processMobileMoneyPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'phone_number' => 'required|string',
                'client_id' => 'required|exists:clients,id',
                'business_id' => 'required|exists:businesses,id',
                'items' => 'required|array',
                'invoice_number' => 'nullable|string',
            ]);
            
            $client = Client::find($validated['client_id']);
            $business = \App\Models\Business::find($validated['business_id']);
            
            // Find the invoice if invoice_number is provided
            $invoice = null;
            if (!empty($validated['invoice_number'])) {
                $invoice = \App\Models\Invoice::where('invoice_number', $validated['invoice_number'])
                    ->where('client_id', $validated['client_id'])
                    ->where('business_id', $validated['business_id'])
                    ->first();
                
                if ($invoice) {
                    Log::info("Found invoice for mobile money payment", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'client_id' => $client->id
                    ]);
                } else {
                    Log::warning("Invoice not found for mobile money payment", [
                        'invoice_number' => $validated['invoice_number'],
                        'client_id' => $validated['client_id'],
                        'business_id' => $validated['business_id']
                    ]);
                }
            }
            
            // Build comprehensive payment description
            $description = $this->buildItemsDescription(
                $validated['items'], 
                $client, 
                $business, 
                $validated['invoice_number']
            );
            
            // Format phone number for mobile money API
            $phone = $validated['phone_number'];
            
            // Remove any non-numeric characters except + at the beginning
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Handle different phone number formats
            if (str_starts_with($phone, '+256')) {
                // Remove the + prefix for YoAPI
                $phone = substr($phone, 1);
            } elseif (str_starts_with($phone, '256')) {
                // Already in correct format
                $phone = $phone;
            } elseif (str_starts_with($phone, '0')) {
                // Convert from local format (0XXXXXXXXX) to international (256XXXXXXXXX)
                $phone = '256' . substr($phone, 1);
            } else {
                // Assume it's already in international format without +
                $phone = $phone;
            }
            
            // Initialize YoAPI for mobile money payment
            $yoPayments = new \App\Payments\YoAPI(config('payments.yo_username'), config('payments.yo_password'));
            $yoPayments->set_instant_notification_url('https://webhook.site/396126eb-cc9b-4c57-a7a9-58f43d2b7935');
            $yoPayments->set_external_reference(uniqid());
            
            // Log payment attempt details
            Log::info('Mobile money payment attempt', [
                'original_phone' => $validated['phone_number'],
                'formatted_phone' => $phone,
                'amount' => $validated['amount'],
                'description' => $description,
                'description_length' => strlen($description),
                'client_id' => $validated['client_id'],
                'business_id' => $validated['business_id'],
                'yo_username' => config('payments.yo_username') ? 'SET' : 'NOT SET',
                'yo_password' => config('payments.yo_password') ? 'SET' : 'NOT SET'
            ]);
            
            // Log exact parameters being sent to YoAPI
            Log::info('=== YOAPI REQUEST PARAMETERS ===', [
                'phone' => $phone,
                'amount' => $validated['amount'],
                'narrative' => $description,
                'narrative_length' => strlen($description),
                'webhook_url' => 'https://webhook.site/396126eb-cc9b-4c57-a7a9-58f43d2b7935',
                'external_reference' => 'will_be_generated',
                'credentials_configured' => [
                    'username' => config('payments.yo_username') ? substr(config('payments.yo_username'), 0, 3) . '***' : 'MISSING',
                    'password' => config('payments.yo_password') ? '***SET***' : 'MISSING'
                ]
            ]);
            
            // Process payment through YoAPI
            $result = $yoPayments->ac_deposit_funds($phone, $validated['amount'], $description);
            
            // Log the actual API response
            Log::info('YoAPI actual response', ['result' => $result]);
            
            // Check if payment request was initiated successfully
            if (isset($result['Status']) && $result['Status'] === 'OK' && isset($result['TransactionReference'])) {
                
                Log::info("Mobile money payment request initiated successfully", [
                    'transaction_reference' => $result['TransactionReference'],
                    'phone' => $phone,
                    'amount' => $validated['amount'],
                    'client_id' => $validated['client_id'],
                    'invoice_number' => $validated['invoice_number']
                ]);
                
                // Create transaction record for tracking
                $transaction = \App\Models\Transaction::create([
                    'business_id' => $validated['business_id'],
                    'branch_id' => $client->branch_id ?? null,
                    'client_id' => $validated['client_id'],
                    'invoice_id' => $invoice ? $invoice->id : null, // Link to invoice if found
                    'amount' => $validated['amount'],
                    'reference' => $validated['invoice_number'], // Use invoice number as reference
                    'external_reference' => $result['TransactionReference'], // Store YoAPI TransactionReference
                    'description' => $description,
                    'status' => 'pending',
                    'type' => 'debit',
                    'origin' => 'web',
                    'phone_number' => $validated['phone_number'],
                    'provider' => 'yo',
                    'service' => 'mobile_money_payment',
                    'date' => now(),
                    'currency' => 'UGX',
                    'names' => $client->name,
                    'email' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'method' => 'mobile_money',
                    'transaction_for' => 'main',
                ]);
                
                Log::info("Transaction record created for mobile money payment", [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $invoice ? $invoice->id : null,
                    'external_reference' => $result['TransactionReference'],
                    'status' => 'pending'
                ]);
                
                return response()->json([
                    'success' => true,
                    'transaction_id' => $result['TransactionReference'],
                    'status' => 'pending',
                    'message' => 'A payment prompt has been sent to your phone. Please complete the payment to proceed.',
                    'description' => $description,
                    'yoapi_response' => $result,
                    'internal_transaction_id' => $transaction->id
                ]);
            } else {
                $errorMessage = isset($result['StatusMessage']) ? "Mobile Money payment failed: {$result['StatusMessage']}" : 
                               (isset($result['ErrorMessage']) ? "Mobile Money payment failed: {$result['ErrorMessage']}" : 
                               'Mobile Money payment failed: Unknown error.');
                
                Log::error('Mobile money payment failed', [
                    'result' => $result,
                    'phone' => $phone,
                    'amount' => $validated['amount'],
                    'error_message' => $errorMessage
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'yoapi_response' => $result
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Mobile money payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing mobile money payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reinitiate a failed mobile money transaction
     */
    public function reinitiateFailedTransaction(Request $request)
    {
        try {
            Log::info('=== RETRY TRANSACTION REQUEST STARTED ===', [
                'timestamp' => now()->toDateTimeString(),
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'request_data' => $request->all()
            ]);
            
            $validated = $request->validate([
                'transaction_id' => 'required|exists:transactions,id',
            ]);
            
            Log::info('Request validation passed', [
                'validated_transaction_id' => $validated['transaction_id']
            ]);
            
            $transaction = \App\Models\Transaction::find($validated['transaction_id']);
            
            // Log transaction details
            Log::info('Transaction found for retry', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
                'status' => $transaction->status,
                'method' => $transaction->method,
                'provider' => $transaction->provider,
                'amount' => $transaction->amount,
                'phone_number' => $transaction->phone_number,
                'external_reference' => $transaction->external_reference,
                'client_id' => $transaction->client_id,
                'business_id' => $transaction->business_id,
                'invoice_id' => $transaction->invoice_id,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at
            ]);
            
            // Check if transaction is failed
            if ($transaction->status !== 'failed') {
                Log::warning('Retry attempted on non-failed transaction', [
                    'transaction_id' => $transaction->id,
                    'current_status' => $transaction->status,
                    'expected_status' => 'failed'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not in failed status and cannot be reinitiated.'
                ], 400);
            }
            
            // Check if transaction is mobile money
            if ($transaction->method !== 'mobile_money' || $transaction->provider !== 'yo') {
                Log::warning('Retry attempted on non-YoAPI mobile money transaction', [
                    'transaction_id' => $transaction->id,
                    'method' => $transaction->method,
                    'provider' => $transaction->provider
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only YoAPI mobile money transactions can be reinitiated.'
                ], 400);
            }
            
            Log::info("Reinitiating failed transaction", [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
                'external_reference' => $transaction->external_reference,
                'amount' => $transaction->amount,
                'client_id' => $transaction->client_id
            ]);
            
            // Get client and business
            $client = $transaction->client;
            $business = $transaction->business;
            
            if (!$client || !$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client or business not found for this transaction.'
                ], 400);
            }
            
            // Format phone number for mobile money API
            $phone = $transaction->phone_number;
            
            // Remove any non-numeric characters except + at the beginning
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Handle different phone number formats
            if (str_starts_with($phone, '+256')) {
                // Remove the + prefix for YoAPI
                $phone = substr($phone, 1);
            } elseif (str_starts_with($phone, '256')) {
                // Already in correct format
                $phone = $phone;
            } elseif (str_starts_with($phone, '0')) {
                // Convert from local format (0XXXXXXXXX) to international (256XXXXXXXXX)
                $phone = '256' . substr($phone, 1);
            } else {
                // Assume it's already in international format without +
                $phone = $phone;
            }
            
            // Generate a new unique external reference for the retry
            // This prevents YoAPI duplicate transaction errors
            $newExternalReference = 'RETRY_' . $transaction->reference . '_' . time() . '_' . uniqid();
            
            // Initialize YoAPI for mobile money payment
            $yoPayments = new \App\Payments\YoAPI(config('payments.yo_username'), config('payments.yo_password'));
            $yoPayments->set_instant_notification_url('https://webhook.site/396126eb-cc9b-4c57-a7a9-58f43d2b7935');
            $yoPayments->set_external_reference($newExternalReference);
            
            Log::info('YoAPI initialized for retry', [
                'yo_username' => config('payments.yo_username'),
                'yo_password_set' => !empty(config('payments.yo_password')),
                'old_external_reference' => $transaction->external_reference,
                'new_external_reference' => $newExternalReference,
                'transaction_reference' => $transaction->reference,
                'notification_url' => 'https://webhook.site/396126eb-cc9b-4c57-a7a9-58f43d2b7935'
            ]);
            
            Log::info('Reinitiating mobile money payment', [
                'original_phone' => $transaction->phone_number,
                'formatted_phone' => $phone,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'client_id' => $transaction->client_id,
                'business_id' => $transaction->business_id,
                'old_external_reference' => $transaction->external_reference,
                'new_external_reference' => $newExternalReference,
                'transaction_reference' => $transaction->reference
            ]);
            
            // Process payment through YoAPI
            Log::info('Calling YoAPI ac_deposit_funds', [
                'phone' => $phone,
                'amount' => $transaction->amount,
                'description' => $transaction->description
            ]);
            
            $result = $yoPayments->ac_deposit_funds($phone, $transaction->amount, $transaction->description);
            
            // Log the actual API response
            Log::info('YoAPI reinitiation response received', [
                'result' => $result,
                'response_type' => gettype($result),
                'response_size' => is_string($result) ? strlen($result) : (is_array($result) ? count($result) : 'unknown')
            ]);
            
            // Check if payment request was initiated successfully
            Log::info('Checking YoAPI response for success', [
                'has_status' => isset($result['Status']),
                'status_value' => $result['Status'] ?? 'not_set',
                'has_transaction_reference' => isset($result['TransactionReference']),
                'transaction_reference' => $result['TransactionReference'] ?? 'not_set',
                'full_response' => $result
            ]);
            
            if (isset($result['Status']) && $result['Status'] === 'OK' && isset($result['TransactionReference'])) {
                
                Log::info("✅ YoAPI retry SUCCESS - Failed transaction reinitiated successfully", [
                    'old_transaction_id' => $transaction->id,
                    'new_transaction_reference' => $result['TransactionReference'],
                    'phone' => $phone,
                    'amount' => $transaction->amount,
                    'client_id' => $transaction->client_id,
                    'business_id' => $transaction->business_id
                ]);
                
                // Update the existing transaction with new external reference and reset status
                // This updates the SAME transaction, does NOT create a new one
                $oldExternalReference = $transaction->external_reference;
                
                $transaction->update([
                    'external_reference' => $result['TransactionReference'],
                    'status' => 'pending',
                    'updated_at' => now()
                ]);
                
                Log::info("✅ Existing transaction updated (NOT creating new transaction)", [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'old_external_reference' => $oldExternalReference,
                    'our_new_external_reference' => $newExternalReference,
                    'yoapi_transaction_reference' => $result['TransactionReference'],
                    'status_changed_from' => 'failed',
                    'status_changed_to' => 'pending'
                ]);
                
                // Update related invoice if exists
                if ($transaction->invoice_id) {
                    $invoice = \App\Models\Invoice::find($transaction->invoice_id);
                    if ($invoice) {
                        $invoice->update([
                            'status' => 'pending',
                            'payment_status' => 'pending'
                        ]);
                        
                        Log::info("Invoice status updated to pending for reinitiated transaction", [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'transaction_id' => $transaction->id
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'transaction_id' => $result['TransactionReference'],
                    'status' => 'pending',
                    'message' => 'Transaction reinitiated successfully. A payment prompt has been sent to your phone. Please complete the payment to proceed.',
                    'description' => $transaction->description,
                    'yoapi_response' => $result,
                    'internal_transaction_id' => $transaction->id
                ]);
            } else {
                $errorMessage = isset($result['StatusMessage']) ? "Transaction reinitiation failed: {$result['StatusMessage']}" : 
                               (isset($result['ErrorMessage']) ? "Transaction reinitiation failed: {$result['ErrorMessage']}" : 
                               'Transaction reinitiation failed: Unknown error.');
                
                Log::error('❌ YoAPI retry FAILED - Failed transaction reinitiation failed', [
                    'result' => $result,
                    'phone' => $phone,
                    'amount' => $transaction->amount,
                    'error_message' => $errorMessage,
                    'transaction_id' => $transaction->id,
                    'status_code' => $result['StatusCode'] ?? 'not_set',
                    'status_message' => $result['StatusMessage'] ?? 'not_set',
                    'transaction_status' => $result['TransactionStatus'] ?? 'not_set',
                    'error_message_field' => $result['ErrorMessage'] ?? 'not_set'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'yoapi_response' => $result
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('💥 EXCEPTION in retry transaction', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'transaction_id' => $transactionId ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error reinitiating transaction: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reinitiate a failed invoice (all failed transactions associated with it)
     */
    public function reinitiateFailedInvoice(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
            ]);
            
            $invoice = \App\Models\Invoice::find($validated['invoice_id']);
            
            // Check if invoice has failed transactions
            $failedTransactions = $invoice->transactions()
                ->where('status', 'failed')
                ->where('method', 'mobile_money')
                ->where('provider', 'yo')
                ->get();
            
            if ($failedTransactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No failed mobile money transactions found for this invoice.'
                ], 400);
            }
            
            Log::info("Reinitiating failed invoice", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'failed_transactions_count' => $failedTransactions->count()
            ]);
            
            $reinitiatedCount = 0;
            $errors = [];
            
            foreach ($failedTransactions as $transaction) {
                try {
                    // Use the same logic as reinitiateFailedTransaction but without the validation
                    $client = $transaction->client;
                    $business = $transaction->business;
                    
                    if (!$client || !$business) {
                        $errors[] = "Client or business not found for transaction {$transaction->id}";
                        continue;
                    }
                    
                    // Format phone number for mobile money API
                    $phone = $transaction->phone_number;
                    $phone = preg_replace('/[^0-9+]/', '', $phone);
                    
                    if (str_starts_with($phone, '+256')) {
                        $phone = substr($phone, 1);
                    } elseif (str_starts_with($phone, '0')) {
                        $phone = '256' . substr($phone, 1);
                    }
                    
                    // Initialize YoAPI for mobile money payment
                    $yoPayments = new \App\Payments\YoAPI(config('payments.yo_username'), config('payments.yo_password'));
                    $yoPayments->set_instant_notification_url('https://webhook.site/396126eb-cc9b-4c57-a7a9-58f43d2b7935');
                    $yoPayments->set_external_reference(uniqid());
                    
                    // Process payment through YoAPI
                    $result = $yoPayments->ac_deposit_funds($phone, $transaction->amount, $transaction->description);
                    
                    if (isset($result['Status']) && $result['Status'] === 'OK' && isset($result['TransactionReference'])) {
                        // Update the transaction with new external reference and reset status
                        $transaction->update([
                            'external_reference' => $result['TransactionReference'],
                            'status' => 'pending',
                            'updated_at' => now()
                        ]);
                        
                        $reinitiatedCount++;
                        
                        Log::info("Transaction reinitiated successfully in invoice reinitiation", [
                            'transaction_id' => $transaction->id,
                            'new_external_reference' => $result['TransactionReference']
                        ]);
                    } else {
                        $errorMessage = isset($result['StatusMessage']) ? $result['StatusMessage'] : 
                                       (isset($result['ErrorMessage']) ? $result['ErrorMessage'] : 'Unknown error');
                        $errors[] = "Transaction {$transaction->id}: {$errorMessage}";
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Transaction {$transaction->id}: {$e->getMessage()}";
                    Log::error("Error reinitiating transaction {$transaction->id} in invoice reinitiation", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update invoice status if at least one transaction was reinitiated
            if ($reinitiatedCount > 0) {
                $invoice->update([
                    'status' => 'pending',
                    'payment_status' => 'pending'
                ]);
                
                Log::info("Invoice status updated to pending after reinitiation", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'reinitiated_transactions' => $reinitiatedCount
                ]);
            }
            
            $response = [
                'success' => $reinitiatedCount > 0,
                'reinitiated_count' => $reinitiatedCount,
                'total_failed_transactions' => $failedTransactions->count(),
                'message' => $reinitiatedCount > 0 ? 
                    "Successfully reinitiated {$reinitiatedCount} transaction(s) for invoice {$invoice->invoice_number}" :
                    "Failed to reinitiate any transactions for invoice {$invoice->invoice_number}"
            ];
            
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
            
            return response()->json($response, $reinitiatedCount > 0 ? 200 : 400);
            
        } catch (\Exception $e) {
            Log::error('Invoice reinitiation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reinitiating invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send receipts for an invoice (testing purposes)
     */
    public function sendReceipts(Invoice $invoice)
    {
        try {
            $user = Auth::user();
            
            // Check if user has access to this invoice
            if ($invoice->business_id !== $user->business_id) {
                abort(403, 'Unauthorized access to invoice.');
            }
            
            Log::info("=== MANUAL RECEIPT SENDING TEST STARTED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => $user->id,
                'client_email' => $invoice->client->email ?? 'no email',
                'business_email' => $invoice->business->email ?? 'no email',
                'kashtre_business_id' => 1,
                'service_charge' => $invoice->service_charge ?? 0,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'kashtre_email' => config('mail.kashtre_email')
                ]
            ]);
            
            $receiptService = new \App\Services\ReceiptService();
            $result = $receiptService->sendElectronicReceipts($invoice);
            
            Log::info("=== MANUAL RECEIPT SENDING TEST COMPLETED ===", [
                'invoice_id' => $invoice->id,
                'result' => $result ? 'success' : 'failed'
            ]);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Receipts sent successfully' : 'Failed to send receipts',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error sending receipts manually', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending receipts: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;
        
        $query = Invoice::where('business_id', $business->id)
            ->with(['client', 'branch', 'createdBy']);
        
        // Filter by client if specified
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by status if specified
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status if specified
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range if specified
        if ($request->has('date_filter') && $request->date_filter !== '') {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%");
            });
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('invoices.index', compact('invoices'));
    }
    
    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $user = Auth::user();
        
        // Check if user has access to this invoice
        if ($invoice->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to invoice.');
        }
        
        // Load the invoice with quotations relationship
        $invoice->load('quotations');
        
        return view('invoices.show', compact('invoice'));
    }
    
    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $user = Auth::user();
        
        // Check if user has access to this invoice
        if ($invoice->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to invoice.');
        }
        
        // Mark as printed
        $invoice->markAsPrinted();
        
        return view('invoices.print', compact('invoice'));
    }
    
    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        $user = Auth::user();
        
        // Check if user has access to this invoice
        if ($invoice->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to invoice.');
        }
        
        // Check if invoice can be cancelled
        if ($invoice->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already cancelled.'
            ], 400);
        }
        
        try {
            $invoice->cancel();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Create package tracking records for package items
     */
    private function createPackageTrackingRecords($invoice, $items)
    {
        Log::info("=== STARTING PACKAGE TRACKING CREATION ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items' => count($items)
        ]);
        
        // Use static variable to prevent multiple calls within same request
        static $processedInvoices = [];
        if (in_array($invoice->id, $processedInvoices)) {
            Log::info("⚠️ PACKAGE TRACKING ALREADY PROCESSED FOR THIS INVOICE IN THIS REQUEST", [
                'invoice_id' => $invoice->id,
                'processed_invoices' => $processedInvoices
            ]);
            return;
        }
        
        // Check if package tracking records already exist for this invoice
        $existingRecords = \App\Models\PackageTracking::where('invoice_id', $invoice->id)->count();
        if ($existingRecords > 0) {
            Log::info("⚠️ PACKAGE TRACKING RECORDS ALREADY EXIST IN DATABASE", [
                'invoice_id' => $invoice->id,
                'existing_count' => $existingRecords
            ]);
            $processedInvoices[] = $invoice->id;
            return;
        }
        
        // Mark this invoice as being processed
        $processedInvoices[] = $invoice->id;
        
        $packageTrackingCount = 0;
        
        foreach ($items as $index => $item) {
            Log::info("🔍 PROCESSING ITEM {$index} FOR PACKAGE TRACKING", [
                'item_id' => $item['id'] ?? $item['item_id'] ?? 'null',
                'item_data' => $item,
                'item_name' => $item['name'] ?? 'unknown'
            ]);
            
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            if (!$itemId) {
                Log::info("❌ SKIPPING ITEM - NO ID", ['item' => $item]);
                continue;
            }

            // Get the item from database to check if it's a package
            $itemModel = \App\Models\Item::find($itemId);
            if (!$itemModel) {
                Log::info("❌ SKIPPING ITEM - NOT FOUND IN DATABASE", ['item_id' => $itemId]);
                continue;
            }
            
            if ($itemModel->type !== 'package') {
                Log::info("❌ SKIPPING ITEM - NOT A PACKAGE", [
                    'item_id' => $itemId,
                    'item_type' => $itemModel->type,
                    'item_name' => $itemModel->name
                ]);
                continue;
            }
            
            Log::info("✅ FOUND PACKAGE ITEM", [
                'item_id' => $itemId,
                'item_name' => $itemModel->name,
                'item_type' => $itemModel->type
            ]);

            // Get included items for this package from package_items table
            $packageItems = $itemModel->packageItems()->with('includedItem')->get();
            if ($packageItems->isEmpty()) continue;

            $quantity = $item['quantity'] ?? 1;
            $packagePrice = $item['price'] ?? 0;

            // Create ONE package tracking record per package
            $packageTracking = \App\Models\PackageTracking::create([
                'business_id' => $invoice->business_id,
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'package_item_id' => $itemId,
                'total_quantity' => $quantity, // Total packages purchased
                'used_quantity' => 0,
                'remaining_quantity' => $quantity,
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addDays(365)->toDateString(), // Default 1 year validity
                'status' => 'active',
                'package_price' => $packagePrice,
                'notes' => "Package: {$itemModel->name}, Invoice: {$invoice->invoice_number}",
                'tracking_number' => 'PKG-' . $packageTrackingCount . '-' . now()->format('YmdHis')
            ]);
            
            // Create package tracking items for each included item in the package
            foreach ($packageItems as $packageItem) {
                $includedItem = $packageItem->includedItem;
                $includedItemId = $includedItem->id;
                $maxQuantity = $packageItem->max_quantity ?? 1;
                $includedItemPrice = $includedItem->default_price ?? 0;

                // Calculate total quantity for this included item
                $totalQuantity = $maxQuantity * $quantity;

                // Create package tracking item record
                \App\Models\PackageTrackingItem::create([
                    'package_tracking_id' => $packageTracking->id,
                    'included_item_id' => $includedItemId,
                    'item_name' => $includedItem->name,
                    'item_price' => $includedItemPrice,
                    'max_quantity' => $maxQuantity,
                    'total_quantity' => $totalQuantity,
                    'used_quantity' => 0,
                    'remaining_quantity' => $totalQuantity,
                    'status' => 'active'
                ]);
            }
            
            $packageTrackingCount++;
        }
        
        Log::info("=== PACKAGE TRACKING CREATION COMPLETED ===", [
            'invoice_id' => $invoice->id,
            'package_tracking_count' => $packageTrackingCount
        ]);
    }

    /**
     * Queue items at their respective service points
     */
    private function queueItemsAtServicePoints($invoice, $items)
    {
        Log::info("=== QUEUEING ITEMS AT SERVICE POINTS STARTED ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'items_count' => count($items),
            'items' => $items
        ]);
        
        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            if (!$itemId) {
                Log::warning("Skipping item - no item ID", ['item' => $item]);
                continue;
            }

            // Get the item from database
            $itemModel = \App\Models\Item::find($itemId);
            if (!$itemModel) {
                Log::warning("Skipping item - item model not found", ['item_id' => $itemId]);
                continue;
            }

            $quantity = $item['quantity'] ?? 1;
            
            Log::info("Processing item for service point queuing", [
                'item_id' => $itemId,
                'item_name' => $itemModel->name,
                'item_type' => $itemModel->type,
                'service_point_id' => $itemModel->service_point_id,
                'quantity' => $quantity
            ]);

            // Get service point through BranchServicePoint relationship (same logic as cron job)
            $branchServicePoint = $itemModel->branchServicePoints()
                ->where('business_id', $invoice->business_id)
                ->where('branch_id', $invoice->branch_id)
                ->first();

            Log::info("Found item model and branch service point", [
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

                // Create service delivery queue record for the main item
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

            // Handle package items - queue each included item at its respective service point
            if ($itemModel->type === 'package') {
                Log::info("Processing package item", [
                    'package_item_id' => $itemId,
                    'package_name' => $itemModel->name
                ]);

                $packageItems = $itemModel->packageItems()->with('includedItem')->get();
                
                foreach ($packageItems as $packageItem) {
                    $includedItem = $packageItem->includedItem;
                    $maxQuantity = $packageItem->max_quantity ?? 1;
                    $totalQuantity = $maxQuantity * $quantity;

                    // Get service point for included item through BranchServicePoint relationship
                    $includedBranchServicePoint = $includedItem->branchServicePoints()
                        ->where('business_id', $invoice->business_id)
                        ->where('branch_id', $invoice->branch_id)
                        ->first();

                    Log::info("Found included item service point", [
                        'included_item_id' => $includedItem->id,
                        'included_item_name' => $includedItem->name,
                        'business_id' => $invoice->business_id,
                        'branch_id' => $invoice->branch_id,
                        'branch_service_point_found' => $includedBranchServicePoint ? 'yes' : 'no',
                        'service_point_id' => $includedBranchServicePoint ? $includedBranchServicePoint->service_point_id : null
                    ]);

                    // Only queue if the included item has a service point for this business/branch
                    if ($includedBranchServicePoint && $includedBranchServicePoint->service_point_id) {
                        Log::info("Creating service delivery queue for included item", [
                            'included_item_id' => $includedItem->id,
                            'service_point_id' => $includedBranchServicePoint->service_point_id,
                            'quantity' => $totalQuantity
                        ]);

                        $queueRecord = \App\Models\ServiceDeliveryQueue::create([
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'service_point_id' => $includedBranchServicePoint->service_point_id,
                            'invoice_id' => $invoice->id,
                            'client_id' => $invoice->client_id,
                            'item_id' => $includedItem->id,
                            'item_name' => $includedItem->name,
                            'quantity' => $totalQuantity,
                            'price' => $includedItem->default_price ?? 0,
                            'status' => 'pending',
                            'priority' => 'normal',
                            'notes' => "Package: {$itemModel->name}, Invoice: {$invoice->invoice_number}, Client: {$invoice->client_name}",
                            'queued_at' => now(),
                            'estimated_delivery_time' => now()->addHours(2), // Default 2 hours
                        ]);

                        Log::info("Service delivery queue created for included item", [
                            'queue_id' => $queueRecord->id,
                            'included_item_id' => $includedItem->id,
                            'service_point_id' => $includedBranchServicePoint->service_point_id
                        ]);
                    } else {
                        Log::info("Included item has no service point for this business/branch, skipping queuing", [
                            'included_item_id' => $includedItem->id,
                            'included_item_name' => $includedItem->name,
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'branch_service_point_found' => $includedBranchServicePoint ? 'yes' : 'no'
                        ]);
                    }
                }
            }
        }
        
        Log::info("=== QUEUEING ITEMS AT SERVICE POINTS COMPLETED ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_items_processed' => count($items)
        ]);
    }

    /**
     * Update package usage for items covered by packages
     */
    private function updatePackageUsage($invoice, $items)
    {
        $clientId = $invoice->client_id;
        $businessId = $invoice->business_id;

        // Get client's valid package tracking records
        $validPackages = \App\Models\PackageTracking::where('client_id', $clientId)
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->where('valid_until', '>=', now()->toDateString())
            ->with(['packageItem.packageItems.includedItem'])
            ->get();

        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'];
            $quantity = $item['quantity'] ?? 1;
            $remainingQuantity = $quantity;

            // Check if this item is included in any valid packages
            foreach ($validPackages as $packageTracking) {
                if ($remainingQuantity <= 0) break;

                // Check if the current item is included in this package
                $packageItems = $packageTracking->packageItem->packageItems;
                
                foreach ($packageItems as $packageItem) {
                    if ($packageItem->included_item_id == $itemId) {
                        // Check max quantity constraint from package_items table
                        $maxQuantity = $packageItem->max_quantity ?? null;
                        $fixedQuantity = $packageItem->fixed_quantity ?? null;
                        
                        // Determine how much quantity can be used from this package
                        $availableFromPackage = $packageTracking->remaining_quantity;
                        
                        if ($maxQuantity !== null) {
                            // If max_quantity is set, limit by that
                            $availableFromPackage = min($availableFromPackage, $maxQuantity);
                        } elseif ($fixedQuantity !== null) {
                            // If fixed_quantity is set, use that
                            $availableFromPackage = min($availableFromPackage, $fixedQuantity);
                        }
                        
                        // Calculate how much we can actually use
                        $quantityToUse = min($remainingQuantity, $availableFromPackage);
                        
                        if ($quantityToUse > 0) {
                            // Update package tracking record
                            $packageTracking->useQuantity($quantityToUse);
                            
                            $remainingQuantity -= $quantityToUse;
                            
                            // If we've used all available quantity from this package, break
                            if ($remainingQuantity <= 0) break;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Update client balance when balance adjustment is used
     */
    private function updateClientBalance($client, $balanceAdjustment)
    {
        try {
            $currentBalance = $client->balance ?? 0;
            $newBalance = $currentBalance - $balanceAdjustment;
            
            // Ensure balance doesn't go negative
            $newBalance = max(0, $newBalance);
            
            $client->update(['balance' => $newBalance]);
            
            // DO NOT create balance statement immediately - will be created after payment completion
            Log::info("Balance adjustment used - balance statement will be created after payment completion", [
                'client_id' => $client->id,
                'balance_adjustment' => $balanceAdjustment,
                'invoice_id' => $this->currentInvoice->id ?? null
            ]);
            
            Log::info("Client balance updated: Client ID {$client->id}, Adjustment: {$balanceAdjustment}, Old Balance: {$currentBalance}, New Balance: {$newBalance}");
            
        } catch (\Exception $e) {
            Log::error('Error updating client balance: ' . $e->getMessage());
        }
    }

    /**
     * Manually complete a transaction and send receipts (for testing)
     */
    public function manuallyCompleteTransaction(Invoice $invoice)
    {
        try {
            $user = Auth::user();
            
            // Check if user has access to this invoice
            if ($invoice->business_id !== $user->business_id) {
                abort(403, 'Unauthorized access to invoice.');
            }
            
            Log::info("=== MANUAL TRANSACTION COMPLETION STARTED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => $user->id,
                'current_invoice_status' => $invoice->status,
                'current_payment_status' => $invoice->payment_status
            ]);

            // Update invoice status to paid
            $invoice->update([
                'status' => 'paid',
                'payment_status' => 'paid'
            ]);

            Log::info("Invoice status updated to paid manually", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);

            // Update related transaction if exists
            $transaction = \App\Models\Transaction::where('invoice_id', $invoice->id)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed'
                ]);
                
                Log::info("Transaction status updated to completed manually", [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $invoice->id
                ]);
            }

            // Process payment completion (this will trigger receipts)
            $moneyTrackingService = new \App\Services\MoneyTrackingService();
            
            Log::info("=== PROCESSING PAYMENT COMPLETION MANUALLY ===", [
                'invoice_id' => $invoice->id,
                'items_count' => count($invoice->items ?? [])
            ]);
            
            $balanceStatements = $moneyTrackingService->processPaymentCompleted($invoice, $invoice->items);
            
            Log::info("=== MANUAL PAYMENT COMPLETION FINISHED ===", [
                'invoice_id' => $invoice->id,
                'balance_statements_count' => count($balanceStatements)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction completed manually and receipts sent',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'balance_statements_count' => count($balanceStatements)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error completing transaction manually", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error completing transaction: ' . $e->getMessage(),
                'invoice_id' => $invoice->id
            ], 500);
        }
    }

    /**
     * Test mail configuration
     */
    public function testMail()
    {
        try {
            \Log::info("=== MAIL CONFIGURATION TEST ===", [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password_set' => !empty(config('mail.mailers.smtp.password')),
                'kashtre_email' => config('mail.kashtre_email'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ]);

            // Send a simple test email
            \Mail::raw('This is a test email from KashTre system to verify mail configuration.', function ($message) {
                $message->to('test@example.com')
                        ->subject('KashTre Mail Test');
            });

            return response()->json([
                'success' => true,
                'message' => 'Mail test completed - check logs for configuration details',
                'config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'kashtre_email' => config('mail.kashtre_email')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Mail configuration test failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mail test failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
