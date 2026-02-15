<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPayerBalanceHistory;
use App\Models\ThirdPartyPayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Get invoices for a specific insurance company
     * This fetches invoices from third-party payer balance history
     */
    public function getInvoicesForInsuranceCompany(Request $request, $insuranceCompanyId)
    {
        try {
            Log::info('API: getInvoicesForInsuranceCompany called', [
                'insurance_company_id' => $insuranceCompanyId,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            // Find the third-party payer for this insurance company
            // We need to find all third-party payers with this insurance_company_id
            $thirdPartyPayers = ThirdPartyPayer::where('insurance_company_id', $insuranceCompanyId)
                ->where('type', 'insurance_company')
                ->where('status', 'active')
                ->get();

            Log::info('API: Found third-party payers', [
                'insurance_company_id' => $insuranceCompanyId,
                'count' => $thirdPartyPayers->count(),
            ]);

            if ($thirdPartyPayers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No third-party payer account found for this insurance company.'
                ]);
            }

            // Get all balance history entries (which represent invoices) for these payers
            $balanceHistories = ThirdPartyPayerBalanceHistory::whereIn('third_party_payer_id', $thirdPartyPayers->pluck('id'))
                ->with(['invoice', 'invoice.client', 'client', 'business', 'branch'])
                ->where('transaction_type', 'debit') // Only debit entries (invoices)
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by invoice and format the data
            $invoices = [];
            $processedInvoiceIds = [];

            foreach ($balanceHistories as $history) {
                if (!$history->invoice || in_array($history->invoice_id, $processedInvoiceIds)) {
                    continue;
                }

                $processedInvoiceIds[] = $history->invoice_id;

                // Get all entries for this invoice to calculate totals
                $invoiceEntries = ThirdPartyPayerBalanceHistory::where('invoice_id', $history->invoice_id)
                    ->where('third_party_payer_id', $history->third_party_payer_id)
                    ->get();

                // Calculate totals: debits (invoices) and credits (payments)
                $debits = abs($invoiceEntries->where('transaction_type', 'debit')->sum('change_amount'));
                $credits = $invoiceEntries->where('transaction_type', 'credit')->sum('change_amount');
                
                $totalAmount = $debits;
                $amountPaid = $credits;
                $balanceDue = $totalAmount - $amountPaid;
                
                // Determine payment status
                if ($balanceDue <= 0) {
                    $paymentStatus = 'paid';
                } elseif ($amountPaid > 0) {
                    $paymentStatus = 'partial';
                } else {
                    $paymentStatus = $history->payment_status ?? 'pending_payment';
                }

                $invoices[] = [
                    'id' => $history->invoice_id,
                    'invoice_number' => $history->invoice->invoice_number ?? 'N/A',
                    'client_name' => $history->client ? $history->client->name : ($history->invoice->client_name ?? 'N/A'),
                    'client_id' => $history->client ? $history->client->client_id : null,
                    'client_phone' => $history->client ? $history->client->phone_number : ($history->invoice->client_phone ?? null),
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'balance_due' => max(0, $balanceDue), // Ensure non-negative
                    'payment_status' => $paymentStatus,
                    'status' => $history->invoice->status ?? 'confirmed',
                    'created_at' => $history->invoice->created_at ? $history->invoice->created_at->toDateTimeString() : $history->created_at->toDateTimeString(),
                    'items' => $history->invoice->items ?? [],
                    'business_name' => $history->business ? $history->business->name : null,
                    'branch_name' => $history->branch ? $history->branch->name : null,
                    'third_party_payer_id' => $history->third_party_payer_id,
                    'balance_history_entries' => $invoiceEntries->map(function($entry) {
                        return [
                            'id' => $entry->id,
                            'description' => $entry->description,
                            'amount' => abs($entry->change_amount),
                            'created_at' => $entry->created_at->toDateTimeString(),
                        ];
                    })->toArray(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'message' => 'Invoices retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching invoices for insurance company', [
                'insurance_company_id' => $insuranceCompanyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark an invoice as paid/cleared
     */
    public function markInvoiceAsPaid(Request $request, $invoiceId)
    {
        try {
            $validated = $request->validate([
                'insurance_company_id' => 'required|integer',
                'payment_reference' => 'nullable|string|max:255',
                'payment_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            // Find the third-party payer
            $thirdPartyPayer = ThirdPartyPayer::where('insurance_company_id', $validated['insurance_company_id'])
                ->where('type', 'insurance_company')
                ->where('status', 'active')
                ->first();

            if (!$thirdPartyPayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third-party payer account not found.'
                ], 404);
            }

            // Get all balance history entries for this invoice
            $balanceHistories = ThirdPartyPayerBalanceHistory::where('invoice_id', $invoiceId)
                ->where('third_party_payer_id', $thirdPartyPayer->id)
                ->get();

            if ($balanceHistories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found in balance history.'
                ], 404);
            }

            // Calculate total amount
            $totalAmount = abs($balanceHistories->sum('change_amount'));

            // Get the current balance from the last balance history entry
            $lastBalanceHistory = ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $thirdPartyPayer->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $previousBalance = $lastBalanceHistory ? $lastBalanceHistory->new_balance : ($thirdPartyPayer->current_balance ?? 0);
            $newBalance = $previousBalance + $totalAmount;

            // Create a single credit entry for the total amount (instead of one per debit entry)
            ThirdPartyPayerBalanceHistory::create([
                'third_party_payer_id' => $thirdPartyPayer->id,
                'invoice_id' => $invoiceId,
                'client_id' => $balanceHistories->first()->client_id,
                'business_id' => $balanceHistories->first()->business_id,
                'branch_id' => $balanceHistories->first()->branch_id,
                'user_id' => auth()->id() ?? 1,
                'previous_balance' => $previousBalance,
                'change_amount' => $totalAmount, // Positive for credit
                'new_balance' => $newBalance,
                'transaction_type' => 'credit',
                'description' => "Payment for Invoice #" . ($balanceHistories->first()->invoice->invoice_number ?? 'N/A'),
                'reference_number' => $validated['payment_reference'] ?? ($balanceHistories->first()->invoice->invoice_number ?? null),
                'notes' => $validated['notes'] ?? "Invoice cleared from third-party system",
                'payment_method' => 'bank_transfer', // Default, can be customized
                'payment_status' => 'paid',
            ]);

            // Update third-party payer balance
            $thirdPartyPayer->update(['current_balance' => $newBalance]);

            // Update payment status on balance history entries
            $balanceHistories->each(function($entry) {
                $entry->update(['payment_status' => 'paid']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully.',
                'data' => [
                    'invoice_id' => $invoiceId,
                    'total_amount' => $totalAmount,
                    'new_balance' => $newBalance,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking invoice as paid', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark invoice as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed invoice statement
     */
    public function getInvoiceDetails($invoiceId)
    {
        try {
            // Get invoice with all related data
            $invoice = \App\Models\Invoice::with(['client', 'business', 'branch', 'createdBy'])
                ->findOrFail($invoiceId);

            // Get all balance history entries for this invoice
            $balanceHistories = ThirdPartyPayerBalanceHistory::where('invoice_id', $invoiceId)
                ->with(['thirdPartyPayer', 'client', 'business', 'branch', 'user'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'client_name' => $invoice->client_name,
                        'client_phone' => $invoice->client_phone,
                        'items' => $invoice->items,
                        'subtotal' => $invoice->subtotal,
                        'service_charge' => $invoice->service_charge,
                        'total_amount' => $invoice->total_amount,
                        'amount_paid' => $invoice->amount_paid,
                        'balance_due' => $invoice->balance_due,
                        'payment_methods' => $invoice->payment_methods,
                        'payment_status' => $invoice->payment_status,
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at->toDateTimeString(),
                        'client' => $invoice->client ? [
                            'id' => $invoice->client->id,
                            'client_id' => $invoice->client->client_id,
                            'name' => $invoice->client->name,
                            'phone_number' => $invoice->client->phone_number,
                        ] : null,
                        'business' => $invoice->business ? [
                            'id' => $invoice->business->id,
                            'name' => $invoice->business->name,
                        ] : null,
                        'branch' => $invoice->branch ? [
                            'id' => $invoice->branch->id,
                            'name' => $invoice->branch->name,
                        ] : null,
                    ],
                    'balance_history' => $balanceHistories->map(function($entry) {
                        return [
                            'id' => $entry->id,
                            'transaction_type' => $entry->transaction_type,
                            'description' => $entry->description,
                            'amount' => abs($entry->change_amount),
                            'balance' => $entry->new_balance,
                            'payment_status' => $entry->payment_status,
                            'payment_method' => $entry->payment_method,
                            'reference_number' => $entry->reference_number,
                            'notes' => $entry->notes,
                            'created_at' => $entry->created_at->toDateTimeString(),
                            'third_party_payer' => $entry->thirdPartyPayer ? [
                                'id' => $entry->thirdPartyPayer->id,
                                'name' => $entry->thirdPartyPayer->name,
                            ] : null,
                        ];
                    })->toArray(),
                ],
                'message' => 'Invoice details retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching invoice details', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoice details: ' . $e->getMessage()
            ], 500);
        }
    }
}
