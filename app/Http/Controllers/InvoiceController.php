<?php

namespace App\Http\Controllers;

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
            ]);

            $clientId = $validated['client_id'];
            $businessId = $validated['business_id'];
            $items = $validated['items'];

            $totalAdjustment = 0;
            $adjustmentDetails = [];

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
                
                // Get the item price from the database
                $itemModel = \App\Models\Item::find($itemId);
                $price = $itemModel ? $itemModel->default_price : 0;

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
                                $itemAdjustment = $quantityToUse * $price;
                                $totalAdjustment += $itemAdjustment;
                                
                                $adjustmentDetails[] = [
                                    'item_id' => $itemId,
                                    'item_name' => $item['name'] ?? 'Unknown',
                                    'quantity_adjusted' => $quantityToUse,
                                    'adjustment_amount' => $itemAdjustment,
                                    'package_name' => $packageTracking->packageItem->name,
                                    'package_tracking_id' => $packageTracking->id,
                                    'package_expiry' => $packageTracking->valid_until->format('Y-m-d'),
                                    'remaining_in_package' => $packageTracking->remaining_quantity - $quantityToUse,
                                    'max_quantity' => $maxQuantity,
                                    'fixed_quantity' => $fixedQuantity,
                                ];
                                
                                $remainingQuantity -= $quantityToUse;
                                
                                // If we've used all available quantity from this package, break
                                if ($remainingQuantity <= 0) break;
                            }
                        }
                    }
                }
            }

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
            
            // PACKAGE TRACKING: Create package tracking records for package items
            $this->createPackageTrackingRecords($invoice, $validated['items']);
            
            // PACKAGE ADJUSTMENT: Update package usage for items covered by packages
            $this->updatePackageUsage($invoice, $validated['items']);
            
            // BALANCE ADJUSTMENT: Update client balance if balance adjustment was used
            if ($validated['account_balance_adjustment'] > 0) {
                $this->updateClientBalance($client, $validated['account_balance_adjustment']);
            }
            
            // SERVICE POINT QUEUING: Items will be queued only after payment is completed
            // This is now handled by the CheckPaymentStatus cron job
            
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
     */
    private function buildItemsDescription($items, $client = null, $business = null, $invoiceNumber = null)
    {
        if (empty($items)) {
            return 'Payment for services';
        }
        
        $itemDescriptions = [];
        foreach ($items as $item) {
            // Get the actual Item model to use display_name attribute
            $itemModel = \App\Models\Item::find($item['id'] ?? $item['item_id'] ?? null);
            $name = $itemModel ? $itemModel->display_name : ($item['name'] ?? 'Unknown Item');
            $quantity = $item['quantity'] ?? 1;
            $type = $item['type'] ?? '';
            
            $description = $name;
            if ($quantity > 1) {
                $description .= " (x{$quantity})";
            }
            if (!empty($type)) {
                $description .= " - {$type}";
            }
            
            $itemDescriptions[] = $description;
        }
        
        // Build comprehensive description
        $itemsText = implode(', ', $itemDescriptions);
        
        // Add client information if available
        $clientInfo = '';
        if ($client) {
            $clientInfo = " for {$client->name}";
            if ($client->client_id) {
                $clientInfo .= " (ID: {$client->client_id})";
            }
        }
        
        // Add business information if available
        $businessInfo = '';
        if ($business) {
            $businessInfo = " at {$business->name}";
        }
        
        // Add invoice number if available
        $invoiceInfo = '';
        if ($invoiceNumber) {
            $invoiceInfo = " - Invoice: {$invoiceNumber}";
        }
        
        // Combine all information
        $fullDescription = "Payment for: {$itemsText}{$clientInfo}{$businessInfo}{$invoiceInfo}";
        
        // Limit description length to avoid database issues (mobile money APIs have character limits)
        if (strlen($fullDescription) > 200) {
            // Prioritize items and client info, truncate business/invoice info if needed
            $truncatedItems = substr($itemsText, 0, 150);
            $fullDescription = "Payment for: {$truncatedItems}{$clientInfo}";
            
            if (strlen($fullDescription) > 200) {
                $fullDescription = substr($fullDescription, 0, 197) . '...';
            }
        }
        
        return $fullDescription;
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
            
            if ($branchId) {
                $serviceCharge = ServiceCharge::where('business_id', $businessId)
                    ->where('entity_type', 'branch')
                    ->where('entity_id', $branchId)
                    ->where('is_active', true)
                    ->where('lower_bound', '<=', $subtotal)
                    ->where('upper_bound', '>=', $subtotal)
                    ->first();
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
            }
            
            if (!$serviceCharge) {
                return response()->json([
                    'success' => true,
                    'service_charge' => 0,
                    'message' => 'No service charge configured. Please contact admin to set up service charges.',
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
                'client_id' => $validated['client_id'],
                'business_id' => $validated['business_id']
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
        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            if (!$itemId) continue;

            // Get the item from database to check if it's a package
            $itemModel = \App\Models\Item::find($itemId);
            if (!$itemModel || $itemModel->type !== 'package') continue;

            // Get included items for this package from package_items table
            $packageItems = $itemModel->packageItems()->with('includedItem')->get();
            if ($packageItems->isEmpty()) continue;

            $quantity = $item['quantity'] ?? 1;
            $packagePrice = $item['price'] ?? 0;

            foreach ($packageItems as $packageItem) {
                $includedItem = $packageItem->includedItem;
                $includedItemId = $includedItem->id;
                $maxQuantity = $packageItem->max_quantity ?? 1;
                $includedItemPrice = $includedItem->default_price ?? 0;

                // Calculate total quantity for this included item
                $totalQuantity = $maxQuantity * $quantity;

                // Create package tracking record
                \App\Models\PackageTracking::create([
                    'business_id' => $invoice->business_id,
                    'client_id' => $invoice->client_id,
                    'invoice_id' => $invoice->id,
                    'package_item_id' => $itemId,
                    'included_item_id' => $includedItemId,
                    'total_quantity' => $totalQuantity,
                    'used_quantity' => 0,
                    'remaining_quantity' => $totalQuantity,
                    'valid_from' => now()->toDateString(),
                    'valid_until' => now()->addDays(365)->toDateString(), // Default 1 year validity
                    'status' => 'active',
                    'package_price' => $packagePrice,
                    'item_price' => $includedItemPrice,
                    'notes' => "Package: {$itemModel->name}, Invoice: {$invoice->invoice_number}"
                ]);
            }
        }
    }

    /**
     * Queue items at their respective service points
     */
    private function queueItemsAtServicePoints($invoice, $items)
    {
        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'] ?? null;
            if (!$itemId) continue;

            // Get the item from database
            $itemModel = \App\Models\Item::find($itemId);
            if (!$itemModel) continue;

            $quantity = $item['quantity'] ?? 1;

            // Handle regular items with service points
            if ($itemModel->service_point_id) {
                // Create service delivery queue record for the main item
                \App\Models\ServiceDeliveryQueue::create([
                    'business_id' => $invoice->business_id,
                    'branch_id' => $invoice->branch_id,
                    'service_point_id' => $itemModel->service_point_id,
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
            }

            // Handle package items - queue each included item at its respective service point
            if ($itemModel->type === 'package') {
                $packageItems = $itemModel->packageItems()->with('includedItem')->get();
                
                foreach ($packageItems as $packageItem) {
                    $includedItem = $packageItem->includedItem;
                    $maxQuantity = $packageItem->max_quantity ?? 1;
                    $totalQuantity = $maxQuantity * $quantity;

                    // Only queue if the included item has a service point
                    if ($includedItem->service_point_id) {
                        \App\Models\ServiceDeliveryQueue::create([
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'service_point_id' => $includedItem->service_point_id,
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
                    }
                }
            }
        }
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
}
