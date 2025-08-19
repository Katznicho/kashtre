<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Store a new invoice
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $business = $user->business;
            
            // Validate request
            $validated = $request->validate([
                'invoice_number' => 'required|string|unique:invoices,invoice_number',
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
            
            // Calculate service charge based on business settings
            $calculatedServiceCharge = $this->calculateServiceCharge($business->id, $validated['subtotal']);
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
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
                'service_charge' => $calculatedServiceCharge,
                'total_amount' => $validated['subtotal'] + $calculatedServiceCharge,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'balance_due' => ($validated['subtotal'] + $calculatedServiceCharge) - ($validated['amount_paid'] ?? 0),
                'payment_methods' => $validated['payment_methods'] ?? [],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? '',
                'confirmed_at' => $validated['status'] === 'confirmed' ? now() : null,
            ]);
            
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
    public function index()
    {
        $user = Auth::user();
        $business = $user->business;
        
        $invoices = Invoice::where('business_id', $business->id)
            ->with(['client', 'branch', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
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
}
