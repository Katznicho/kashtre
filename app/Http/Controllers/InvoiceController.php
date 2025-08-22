<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceCharge;
use App\Models\Client;
use App\Services\MoneyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
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

            // For now, we'll implement a simple package adjustment calculation
            // This will be enhanced once the PackageUsage model is properly set up
            $totalAdjustment = 0;
            $adjustmentDetails = [];

            // Get client's previous invoices with packages
            $previousInvoices = Invoice::where('client_id', $clientId)
                ->where('business_id', $businessId)
                ->where('status', '!=', 'cancelled')
                ->whereJsonLength('items', '>', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($items as $item) {
                $itemId = $item['id'] ?? $item['item_id'];
                $quantity = $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;

                // Check if this item is available in any previous package purchases
                foreach ($previousInvoices as $invoice) {
                    $invoiceItems = $invoice->items ?? [];
                    
                    foreach ($invoiceItems as $invoiceItem) {
                        $invoiceItemId = $invoiceItem['id'] ?? $invoiceItem['item_id'];
                        
                        // If this item was part of a package in a previous invoice
                        if ($invoiceItemId == $itemId && isset($invoiceItem['package_info'])) {
                            $packageInfo = $invoiceItem['package_info'];
                            $packageExpiry = $packageInfo['expiry_date'] ?? null;
                            
                            // Check if package is still valid
                            if ($packageExpiry && now()->lt($packageExpiry)) {
                                $availableQuantity = $packageInfo['available_quantity'] ?? 0;
                                
                                if ($availableQuantity > 0) {
                                    $quantityToUse = min($quantity, $availableQuantity);
                                    $itemAdjustment = $quantityToUse * $price;
                                    $totalAdjustment += $itemAdjustment;
                                    
                                    $adjustmentDetails[] = [
                                        'item_id' => $itemId,
                                        'item_name' => $item['name'] ?? 'Unknown',
                                        'quantity_adjusted' => $quantityToUse,
                                        'adjustment_amount' => $itemAdjustment,
                                        'package_invoice' => $invoice->invoice_number,
                                        'package_expiry' => $packageExpiry,
                                    ];
                                    
                                    // Update the quantity for this item
                                    $quantity -= $quantityToUse;
                                    
                                    // If we've used all available quantity, break
                                    if ($quantity <= 0) break;
                                }
                            }
                        }
                    }
                    
                    // If we've processed all items, break
                    if ($quantity <= 0) break;
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
            DB::beginTransaction();
            
            $user = Auth::user();
            $business = $user->business;
            $moneyTrackingService = new MoneyTrackingService();
            
            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber($business->id);
            
            // Validate request
            $validated = $request->validate([
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
            
            // MONEY TRACKING: Step 1 - Process payment received
            if ($validated['amount_paid'] > 0) {
                $paymentMethods = $validated['payment_methods'] ?? [];
                $primaryMethod = !empty($paymentMethods) ? $paymentMethods[0] : 'cash';
                
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
                
                // Create transaction record for the payment
                \App\Models\Transaction::create([
                    'business_id' => $validated['business_id'],
                    'amount' => $validated['amount_paid'],
                    'reference' => $invoiceNumber,
                    'description' => 'Payment for invoice ' . $invoiceNumber . ' - ' . $validated['client_name'],
                    'status' => 'completed',
                    'type' => 'debit',
                    'origin' => 'web',
                    'phone_number' => $validated['payment_phone'] ?? $validated['client_phone'],
                    'provider' => in_array($primaryMethod, ['mtn', 'airtel']) ? $primaryMethod : 'mtn',
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
            }
            
            // MONEY TRACKING: Step 2 - Process order confirmation (move money to suspense accounts)
            if ($validated['status'] === 'confirmed') {
                $items = $validated['items'];
                $moneyTrackingService->processOrderConfirmed($invoice, $items);
            }
            
            // MONEY TRACKING: Step 3 - Process service charge
            if ($validated['service_charge'] > 0) {
                $moneyTrackingService->processServiceCharge($invoice, $validated['service_charge']);
            }
            
            // Generate next invoice number
            $nextInvoiceNumber = Invoice::generateInvoiceNumber($business->id);
            
            DB::commit();
            
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
     * Calculate service charge for a business
     */
    public function calculateServiceCharge($businessId, $subtotal)
    {
        // Get service charge settings for the business
        $serviceCharge = ServiceCharge::where('business_id', $businessId)
            ->where('entity_type', 'business')
            ->where('is_active', true)
            ->first();
        
        if (!$serviceCharge) {
            return 0; // No service charge configured
        }
        
        // Calculate based on type and amount
        if ($serviceCharge->type === 'percentage') {
            return ($subtotal * $serviceCharge->amount) / 100;
        } else {
            return $serviceCharge->amount;
        }
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
                    ->first();
            }
            
            // If no branch service charge, try business level
            if (!$serviceCharge) {
                $serviceCharge = ServiceCharge::where('business_id', $businessId)
                    ->where('entity_type', 'business')
                    ->where('is_active', true)
                    ->first();
            }
            
            if (!$serviceCharge) {
                return response()->json([
                    'success' => true,
                    'service_charge' => 0,
                    'message' => 'No service charge configured. Please contact admin to set up service charges.',
                ]);
            }
            
            // Calculate service charge based on type and bounds
            $calculatedCharge = 0;
            
            if ($serviceCharge->type === 'percentage') {
                $calculatedCharge = ($subtotal * $serviceCharge->amount) / 100;
            } else {
                $calculatedCharge = $serviceCharge->amount;
            }
            
            // Apply bounds if set
            if ($serviceCharge->lower_bound !== null && $calculatedCharge < $serviceCharge->lower_bound) {
                $calculatedCharge = $serviceCharge->lower_bound;
            }
            
            if ($serviceCharge->upper_bound !== null && $calculatedCharge > $serviceCharge->upper_bound) {
                $calculatedCharge = $serviceCharge->upper_bound;
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
     * Generate next invoice number
     */
    public function generateInvoiceNumber(Request $request)
    {
        $businessId = $request->input('business_id');
        $invoiceNumber = Invoice::generateInvoiceNumber($businessId);
        
        return response()->json([
            'invoice_number' => $invoiceNumber,
        ]);
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
}
