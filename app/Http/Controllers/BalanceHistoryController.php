<?php

namespace App\Http\Controllers;

use App\Models\BalanceHistory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceHistoryController extends Controller
{
    /**
     * Display balance statement for all clients or a specific client
     */
    public function index(Request $request)
    {
        // Show all balance histories for the current business
        $businessId = Auth::user()->business_id;
        $balanceHistories = BalanceHistory::where('business_id', $businessId)
            ->with(['client', 'user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance-statement.index', compact('balanceHistories'));
    }

    /**
     * Show balance statement for a specific client
     */
    public function show($clientId)
    {
        $client = Client::findOrFail($clientId);
        
        $balanceHistories = BalanceHistory::where('client_id', $clientId)
            ->with(['user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance-statement.show', compact('balanceHistories', 'client'));
    }

    /**
     * Get balance statement as JSON for AJAX requests
     */
    public function getBalanceHistory(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
        
        $balanceHistories = BalanceHistory::where('client_id', $clientId)
            ->with(['user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'date' => $history->created_at->format('Y-m-d H:i:s'),
                    'transaction_type' => $history->transaction_type,
                    'description' => $history->description,
                    'previous_balance' => number_format($history->previous_balance, 2),
                    'change_amount' => $history->getFormattedChangeAmount(),
                    'new_balance' => number_format($history->new_balance, 2),
                    'reference_number' => $history->reference_number,
                    'user_name' => $history->user ? $history->user->name : 'System',
                    'payment_method' => $history->payment_method,
                    'notes' => $history->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $balanceHistories,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'current_balance' => number_format($client->balance ?? 0, 2),
            ]
        ]);
    }

    /**
     * Get PP (Pending Payment) entries for a client
     * Ordered: Service charges first, then other items oldest to newest
     */
    public function getPPEntries($clientId)
    {
        $client = Client::findOrFail($clientId);
        
        // Get all debit entries from invoices with pending_payment status
        $ppEntries = BalanceHistory::where('client_id', $clientId)
            ->where('transaction_type', 'debit')
            ->whereNotNull('invoice_id')
            ->with(['invoice'])
            ->get()
            ->filter(function ($entry) {
                // Only include entries from invoices with pending payment status
                return $entry->invoice && 
                       $entry->invoice->payment_status === 'pending_payment' &&
                       $entry->invoice->balance_due > 0;
            })
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'description' => $entry->description,
                    'amount' => abs($entry->change_amount), // Make positive
                    'date' => $entry->created_at->format('Y-m-d H:i'),
                    'invoice_id' => $entry->invoice_id,
                    'invoice_number' => $entry->invoice->invoice_number ?? null,
                    'is_service_charge' => stripos($entry->description, 'service fee') !== false || 
                                          stripos($entry->description, 'service charge') !== false,
                    'created_at' => $entry->created_at->timestamp,
                ];
            })
            ->sortBy(function ($entry) {
                // Service charges first (priority 0), then others (priority 1)
                $priority = $entry['is_service_charge'] ? 0 : 1;
                // Then by date (oldest first)
                return [$priority, $entry['created_at']];
            })
            ->values();

        return response()->json([
            'success' => true,
            'entries' => $ppEntries,
            'total_outstanding' => $ppEntries->sum('amount'),
        ]);
    }

    /**
     * Process payment for PP entries
     */
    public function payBack(Request $request, $clientId)
    {
        $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:balance_histories,id',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer,card',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $client = Client::findOrFail($clientId);
        $entryIds = $request->entry_ids;
        $paymentMethod = $request->payment_method;
        $totalAmount = $request->total_amount;

        \DB::beginTransaction();
        try {
            // Get the selected entries
            $entries = BalanceHistory::whereIn('id', $entryIds)
                ->where('client_id', $clientId)
                ->where('transaction_type', 'debit')
                ->with(['invoice'])
                ->get();

            if ($entries->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid entries found to pay.'
                ], 400);
            }

            // Group entries by invoice
            $invoicesToUpdate = [];
            $paidAmountsByInvoice = [];

            foreach ($entries as $entry) {
                if ($entry->invoice) {
                    $invoiceId = $entry->invoice->id;
                    if (!isset($paidAmountsByInvoice[$invoiceId])) {
                        $paidAmountsByInvoice[$invoiceId] = 0;
                        $invoicesToUpdate[$invoiceId] = $entry->invoice;
                    }
                    $paidAmountsByInvoice[$invoiceId] += abs($entry->change_amount);
                }
            }

            // Create payment description
            $itemDescriptions = $entries->pluck('description')->toArray();
            $description = "Payment for PP items: " . implode(", ", array_slice($itemDescriptions, 0, 3));
            if (count($itemDescriptions) > 3) {
                $description .= " and " . (count($itemDescriptions) - 3) . " more";
            }

            // Debit from source account (payment method account)
            // Normalize payment method for lookup (e.g., 'mobile_money' -> 'mobile money')
            $normalizedPaymentMethod = str_replace('_', ' ', $paymentMethod);
            $sourceAccount = \App\Models\PaymentMethodAccount::forBusiness($client->business_id)
                ->forPaymentMethod($normalizedPaymentMethod)
                ->active()
                ->first();

            if ($sourceAccount) {
                $sourceAccount->debit(
                    $totalAmount,
                    'PP-PAY-' . time(),
                    $description,
                    $client->id,
                    null,
                    ['type' => 'pp_payment', 'entry_ids' => $entryIds]
                );
                \Log::info("Debited payment method account for PP payment", [
                    'account_id' => $sourceAccount->id,
                    'amount' => $totalAmount,
                ]);
            } else {
                \Log::warning("Payment method account not found for PP payment", [
                    'business_id' => $client->business_id,
                    'payment_method' => $normalizedPaymentMethod,
                ]);
            }

            // Credit client account
            \App\Models\BalanceHistory::recordCredit(
                $client,
                $totalAmount,
                $description,
                'PP-PAY-' . time(),
                "Payment for pending payment items",
                $paymentMethod
            );

            // Update client balance (reduce negative balance)
            $client->increment('balance', $totalAmount);

            // Update invoices and accounts receivable
            foreach ($invoicesToUpdate as $invoiceId => $invoice) {
                $paidAmount = $paidAmountsByInvoice[$invoiceId];
                
                // Update invoice
                $invoice->increment('amount_paid', $paidAmount);
                $invoice->decrement('balance_due', $paidAmount);
                
                // If invoice is fully paid, update payment status
                $invoice->refresh();
                if ($invoice->balance_due <= 0) {
                    $invoice->update(['payment_status' => 'paid']);
                } elseif ($invoice->amount_paid > 0) {
                    $invoice->update(['payment_status' => 'partial']);
                }

                // Update accounts receivable
                $accountsReceivable = \App\Models\AccountsReceivable::where('invoice_id', $invoiceId)
                    ->where('client_id', $clientId)
                    ->first();

                if ($accountsReceivable) {
                    // Create transaction record for the payment
                    $transaction = \App\Models\Transaction::create([
                        'business_id' => $client->business_id,
                        'branch_id' => $client->branch_id ?? null,
                        'client_id' => $clientId,
                        'invoice_id' => $invoiceId,
                        'amount' => $paidAmount,
                        'reference' => 'PP-PAY-' . time() . '-' . $invoiceId,
                        'description' => $description,
                        'status' => 'completed',
                        'payment_status' => 'Paid',
                        'type' => 'credit',
                        'origin' => 'web',
                        'phone_number' => $client->phone_number ?? null,
                        'provider' => $paymentMethod === 'mobile_money' ? 'yo' : $paymentMethod,
                        'service' => 'pp_payment',
                        'date' => now(),
                        'currency' => 'UGX',
                        'names' => $client->name,
                        'method' => $paymentMethod,
                        'transaction_for' => 'main',
                    ]);
                    
                    $accountsReceivable->recordPayment($paidAmount, $transaction->id);
                }
            }

            \DB::commit();

            \Log::info("PP Payment processed", [
                'client_id' => $clientId,
                'entry_ids' => $entryIds,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Payment of UGX " . number_format($totalAmount, 2) . " processed successfully.",
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error processing PP payment", [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }




}
