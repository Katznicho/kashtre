<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Transaction;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalPaymentController extends Controller
{
    /**
     * Process mobile money payment for local development
     * This bypasses the real YoAPI and creates a pending transaction for testing
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
                $invoice = Invoice::where('invoice_number', $validated['invoice_number'])
                    ->where('client_id', $validated['client_id'])
                    ->where('business_id', $validated['business_id'])
                    ->first();
            }
            
            // Build description from items
            $description = $this->buildItemsDescription($validated['items']);
            
            // Format phone number
            $phone = $this->formatPhoneNumber($validated['phone_number']);
            
            Log::info('=== LOCAL DEVELOPMENT: Mobile Money Payment Simulation ===', [
                'phone' => $phone,
                'amount' => $validated['amount'],
                'description' => $description,
                'client_id' => $validated['client_id'],
                'business_id' => $validated['business_id'],
                'invoice_id' => $invoice ? $invoice->id : null,
                'mode' => 'LOCAL_DEVELOPMENT_SIMULATION'
            ]);
            
            // Generate a fake transaction reference for local testing
            $fakeTransactionReference = 'LOCAL-' . time() . '-' . rand(1000, 9999);
            
            // Determine payment status: PP for credit clients with balance due, otherwise Paid
            $paymentStatus = 'Paid';
            if ($invoice && $client->is_credit_eligible && $invoice->balance_due > 0) {
                $paymentStatus = 'PP';
            }
            
            // Create transaction record as PENDING (this is what we want for testing)
            $transaction = Transaction::create([
                'business_id' => $validated['business_id'],
                'branch_id' => $client->branch_id ?? null,
                'client_id' => $validated['client_id'],
                'invoice_id' => $invoice ? $invoice->id : null,
                'amount' => $validated['amount'],
                'reference' => $validated['invoice_number'],
                'external_reference' => $fakeTransactionReference,
                'description' => $description,
                'status' => 'pending', // This is key - we want it to stay pending for testing
                'payment_status' => $paymentStatus,
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
            
            Log::info("LOCAL DEVELOPMENT: Transaction created as PENDING for testing", [
                'transaction_id' => $transaction->id,
                'external_reference' => $fakeTransactionReference,
                'status' => 'pending',
                'amount' => $validated['amount'],
                'client_id' => $validated['client_id']
            ]);
            
            return response()->json([
                'success' => true,
                'transaction_id' => $fakeTransactionReference,
                'status' => 'pending',
                'message' => 'LOCAL DEV: Payment created as pending. Use testing commands to simulate success.',
                'description' => $description,
                'internal_transaction_id' => $transaction->id,
                'local_development' => true,
                'testing_note' => 'Run: php artisan payments:simulate-success to complete this payment'
            ]);
            
        } catch (\Exception $e) {
            Log::error('LOCAL DEVELOPMENT: Mobile money payment simulation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'LOCAL DEV: Payment simulation failed - ' . $e->getMessage(),
                'local_development' => true
            ], 400);
        }
    }
    
    /**
     * Build items description for payment
     */
    private function buildItemsDescription($items)
    {
        $descriptions = [];
        foreach ($items as $item) {
            $name = $item['name'] ?? 'Unknown Item';
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $descriptions[] = "{$name} (x{$quantity}) - UGX " . number_format($price, 0);
        }
        return implode(', ', $descriptions);
    }
    
    /**
     * Format phone number for local development
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (strpos($phone, '256') !== 0) {
            if (strpos($phone, '0') === 0) {
                $phone = '256' . substr($phone, 1);
            } else {
                $phone = '256' . $phone;
            }
        }
        
        return '+' . $phone;
    }
}
