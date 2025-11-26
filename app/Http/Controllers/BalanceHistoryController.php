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
        
        // Refresh client to ensure we have the latest balance
        $client->refresh();
        
        // Calculate balance from balance history: credits - debits
        // This is the source of truth for account balance
        $credits = BalanceHistory::where('client_id', $clientId)
            ->where('transaction_type', 'credit')
            ->sum('change_amount');
        
        $debits = abs(BalanceHistory::where('client_id', $clientId)
            ->where('transaction_type', 'debit')
            ->sum('change_amount'));
        
        $calculatedBalance = $credits - $debits;
        
        // Update client balance to match calculated balance
        if (abs(($client->balance ?? 0) - $calculatedBalance) >= 0.01) {
            \Log::info("Updating client balance to match balance history calculation", [
                'client_id' => $clientId,
                'current_balance' => $client->balance ?? 0,
                'calculated_balance' => $calculatedBalance,
                'credits' => $credits,
                'debits' => $debits,
            ]);
            $client->update(['balance' => $calculatedBalance]);
            $client->refresh();
        }
        
        $balanceHistories = BalanceHistory::where('client_id', $clientId)
            ->with(['user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance-statement.show', compact('balanceHistories', 'client'));
    }

    /**
     * Show pay back page for a credit client
     */
    public function showPayBack($clientId)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Process Pay Back', $user->permissions ?? [])) {
            return redirect()->route('balance-statement.show', $clientId)
                ->with('error', 'You do not have permission to process pay back.');
        }
        
        $client = Client::findOrFail($clientId);
        
        // Verify client is credit eligible and has negative balance
        if (!$client->is_credit_eligible || ($client->balance ?? 0) >= 0) {
            return redirect()->route('balance-statement.show', $clientId)
                ->with('error', 'This client does not have outstanding amounts to pay back.');
        }
        
        // Get PP entries (service charges first, then oldest to newest)
        // For credit clients with negative balance: Show ALL debit entries from invoices
        // Even if balance_due = 0 (paid upfront), debit entries represent debt to pay back
        $ppEntries = BalanceHistory::where('client_id', $clientId)
            ->where('transaction_type', 'debit')
            ->whereNotNull('invoice_id')
            ->with(['invoice'])
            ->get()
            ->filter(function ($entry) use ($client) {
                if (!$entry->invoice) {
                    return false;
                }
                
                // For credit clients with negative balance, show all debit entries
                // The negative balance indicates outstanding debt regardless of invoice balance_due
                if ($client->is_credit_eligible && $client->balance < 0) {
                    return true; // Show all debit entries for credit clients with debt
                }
                
                // For non-credit clients or credit clients with no debt, only show if balance_due > 0
                return $entry->invoice->balance_due > 0;
            })
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'description' => $entry->description,
                    'amount' => abs($entry->change_amount),
                    'date' => $entry->created_at->format('Y-m-d H:i'),
                    'invoice_id' => $entry->invoice_id,
                    'invoice_number' => $entry->invoice->invoice_number ?? null,
                    'is_service_charge' => stripos($entry->description, 'service fee') !== false || 
                                          stripos($entry->description, 'service charge') !== false,
                    'created_at' => $entry->created_at->timestamp,
                    'item_id' => $entry->invoice->items[0]['id'] ?? $entry->invoice->items[0]['item_id'] ?? null,
                ];
            })
            ->sortBy(function ($entry) {
                $priority = $entry['is_service_charge'] ? 0 : 1;
                return [$priority, $entry['created_at']];
            })
            ->values();

        $totalOutstanding = $ppEntries->sum('amount');

        return view('balance-statement.pay-back', compact('client', 'ppEntries', 'totalOutstanding'));
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
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Process Pay Back', $user->permissions ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to process pay back.'
            ], 403);
        }
        
        $client = Client::findOrFail($clientId);
        
        // Get all debit entries from invoices with outstanding balance
        // For credit clients, any invoice with balance_due > 0 has pending payments
        $ppEntries = BalanceHistory::where('client_id', $clientId)
            ->where('transaction_type', 'debit')
            ->whereNotNull('invoice_id')
            ->with(['invoice'])
            ->get()
            ->filter(function ($entry) {
                // Include all debit entries from invoices with outstanding balance
                // This covers all unpaid items regardless of payment_status
                return $entry->invoice && 
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
     * Creates an invoice without service charge and marks items as paid in destination accounts
     */
    public function payBack(Request $request, $clientId)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Process Pay Back', $user->permissions ?? [])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to process pay back.'
                ], 403);
            }
            return redirect()->route('balance-statement.show', $clientId)
                ->with('error', 'You do not have permission to process pay back.');
        }
        
        $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:balance_histories,id',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer,card',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $client = Client::findOrFail($clientId);
        $business = $client->business;
        $entryIds = $request->entry_ids;
        $paymentMethod = $request->payment_method;
        $totalAmount = $request->total_amount;

        \DB::beginTransaction();
        try {
            // Get the selected entries with invoice and item details
            $entries = BalanceHistory::whereIn('id', $entryIds)
                ->where('client_id', $clientId)
                ->where('transaction_type', 'debit')
                ->with(['invoice'])
                ->get();

            if ($entries->isEmpty()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No valid entries found to pay.'
                    ], 400);
                }
                return redirect()->route('balance-statement.pay-back.show', $clientId)
                    ->with('error', 'No valid entries found to pay.');
            }

            // Build items array for payment invoice (without service charge)
            $paymentItems = [];
            $itemDescriptions = [];
            $invoicesToUpdate = [];
            $paidAmountsByInvoice = [];

            foreach ($entries as $entry) {
                $entryAmount = abs($entry->change_amount);
                $itemDescriptions[] = $entry->description;
                
                // Add to payment items
                $paymentItems[] = [
                    'id' => null, // Payment item, not a product
                    'item_id' => null,
                    'name' => $entry->description,
                    'description' => $entry->description,
                    'quantity' => 1,
                    'price' => $entryAmount,
                    'total_amount' => $entryAmount,
                ];

                // Track invoice payments
                if ($entry->invoice) {
                    $invoiceId = $entry->invoice->id;
                    if (!isset($paidAmountsByInvoice[$invoiceId])) {
                        $paidAmountsByInvoice[$invoiceId] = 0;
                        $invoicesToUpdate[$invoiceId] = $entry->invoice;
                    }
                    $paidAmountsByInvoice[$invoiceId] += $entryAmount;
                }
            }

            // Create payment description
            $description = "Payment for PP items: " . implode(", ", array_slice($itemDescriptions, 0, 3));
            if (count($itemDescriptions) > 3) {
                $description .= " and " . (count($itemDescriptions) - 3) . " more";
            }

            // Generate invoice number for payment invoice
            $paymentInvoiceNumber = \App\Models\Invoice::generateInvoiceNumber($business->id, 'proforma');

            // Create payment invoice (NO SERVICE CHARGE)
            $paymentInvoice = \App\Models\Invoice::create([
                'invoice_number' => $paymentInvoiceNumber,
                'client_id' => $clientId,
                'business_id' => $business->id,
                'branch_id' => $client->branch_id,
                'created_by' => $user->id,
                'client_name' => $client->name,
                'client_phone' => $client->phone_number,
                'payment_phone' => $client->payment_phone_number,
                'visit_id' => $client->visit_id,
                'items' => $paymentItems,
                'subtotal' => $totalAmount,
                'package_adjustment' => 0,
                'account_balance_adjustment' => 0,
                'service_charge' => 0, // NO SERVICE CHARGE for PP payments
                'total_amount' => $totalAmount,
                'amount_paid' => $totalAmount,
                'balance_due' => 0,
                'payment_methods' => [$paymentMethod],
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'notes' => 'Payment for pending payment items - No service charge applied',
            ]);

            \Log::info("Payment invoice created for PP payment", [
                'payment_invoice_id' => $paymentInvoice->id,
                'payment_invoice_number' => $paymentInvoiceNumber,
                'total_amount' => $totalAmount,
                'service_charge' => 0,
            ]);

            // Debit from source account (payment method account)
            $normalizedPaymentMethod = str_replace('_', ' ', $paymentMethod);
            $sourceAccount = \App\Models\PaymentMethodAccount::forBusiness($business->id)
                ->forPaymentMethod($normalizedPaymentMethod)
                ->active()
                ->first();

            if ($sourceAccount) {
                $sourceAccount->debit(
                    $totalAmount,
                    $paymentInvoiceNumber,
                    $description,
                    $client->id,
                    $paymentInvoice->id,
                    ['type' => 'pp_payment', 'entry_ids' => $entryIds]
                );
                \Log::info("Debited payment method account for PP payment", [
                    'account_id' => $sourceAccount->id,
                    'amount' => $totalAmount,
                ]);
            } else {
                \Log::warning("Payment method account not found for PP payment", [
                    'business_id' => $business->id,
                    'payment_method' => $normalizedPaymentMethod,
                ]);
            }

            // Credit client account (reduces debt)
            \App\Models\BalanceHistory::recordCredit(
                $client,
                $totalAmount,
                $description,
                $paymentInvoiceNumber,
                "Payment for pending payment items - Invoice #{$paymentInvoiceNumber}",
                $paymentMethod,
                $paymentInvoice->id
            );

            // Update client balance (reduce negative balance)
            $client->increment('balance', $totalAmount);

            // Process each entry: mark items as paid and move money to destination accounts
            $moneyTrackingService = app(\App\Services\MoneyTrackingService::class);
            
            foreach ($entries as $entry) {
                $entryAmount = abs($entry->change_amount);
                $originalInvoice = $entry->invoice;
                
                if (!$originalInvoice) continue;

                // Find the original item from the invoice
                $itemFound = false;
                foreach ($originalInvoice->items ?? [] as $invoiceItem) {
                    $itemId = $invoiceItem['id'] ?? $invoiceItem['item_id'] ?? null;
                    if (!$itemId) continue;
                    
                    $item = \App\Models\Item::find($itemId);
                    if (!$item) continue;

                    // Check if this entry matches this item
                    $itemDescription = $item->name;
                    $quantity = $invoiceItem['quantity'] ?? 1;
                    $itemTotalAmount = $invoiceItem['total_amount'] ?? ($invoiceItem['price'] ?? 0) * $quantity;
                    
                    // Match by description or amount
                    if (stripos($entry->description, $itemDescription) !== false || 
                        abs($itemTotalAmount - $entryAmount) < 0.01) {
                        
                        // Mark item as paid in destination accounts (entity/contractor/kashtre)
                        // Transfer money from payment method account to destination accounts
                        $this->markItemAsPaid(
                            $moneyTrackingService,
                            $item,
                            $entryAmount,
                            $originalInvoice,
                            $sourceAccount,
                            $paymentInvoiceNumber,
                            $description
                        );
                        
                        $itemFound = true;
                        break;
                    }
                }

                // If item not found but it's a service charge, handle it
                if (!$itemFound && (stripos($entry->description, 'service fee') !== false || 
                                    stripos($entry->description, 'service charge') !== false)) {
                    // Service charge goes to Kashtre account
                    $kashtreAccount = $moneyTrackingService->getOrCreateKashtreAccount();
                    if ($sourceAccount && $kashtreAccount) {
                        $moneyTrackingService->transferMoney(
                            $sourceAccount,
                            $kashtreAccount,
                            $entryAmount,
                            'pp_payment',
                            $paymentInvoice,
                            null,
                            "Service charge payment: {$entry->description}"
                        );
                    }
                }
            }

            // Update original invoices and accounts receivable
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
                        'business_id' => $business->id,
                        'branch_id' => $client->branch_id ?? null,
                        'client_id' => $clientId,
                        'invoice_id' => $invoiceId,
                        'amount' => $paidAmount,
                        'reference' => $paymentInvoiceNumber . '-' . $invoiceId,
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

            \Log::info("PP Payment processed successfully", [
                'client_id' => $clientId,
                'payment_invoice_id' => $paymentInvoice->id,
                'payment_invoice_number' => $paymentInvoiceNumber,
                'entry_ids' => $entryIds,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Payment of UGX " . number_format($totalAmount, 2) . " processed successfully.",
                    'invoice_id' => $paymentInvoice->id,
                    'invoice_number' => $paymentInvoiceNumber,
                ]);
            }

            return redirect()->route('balance-statement.show', $clientId)
                ->with('success', "Payment of UGX " . number_format($totalAmount, 2) . " processed successfully. Invoice #{$paymentInvoiceNumber} created.");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error processing PP payment", [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process payment: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('balance-statement.pay-back.show', $clientId)
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Mark item as paid and transfer money to destination accounts
     */
    private function markItemAsPaid($moneyTrackingService, $item, $amount, $originalInvoice, $sourceAccount, $reference, $description)
    {
        $business = $originalInvoice->business;
        
        // Determine destination account based on item ownership
        if ($item->contractor_account_id) {
            // Contractor item - split between contractor and business
            $contractor = \App\Models\ContractorProfile::find($item->contractor_account_id);
            if ($contractor) {
                $contractorAccount = $moneyTrackingService->getOrCreateContractorAccount($contractor);
                $contractorShare = ($amount * (100 - $item->hospital_share)) / 100;
                $businessShare = $amount - $contractorShare;
                
                // Transfer contractor share
                if ($contractorShare > 0 && $sourceAccount && $contractorAccount) {
                    $moneyTrackingService->transferMoney(
                        $sourceAccount,
                        $contractorAccount,
                        $contractorShare,
                        'pp_payment',
                        $originalInvoice,
                        $item,
                        "PP Payment - Contractor share: {$item->name}"
                    );
                    
                    // Update contractor balance
                    $contractor->increment('account_balance', $contractorShare);
                }
                
                // Transfer business share
                if ($businessShare > 0) {
                    $businessAccount = $moneyTrackingService->getOrCreateBusinessAccount($business);
                    if ($sourceAccount && $businessAccount) {
                        $moneyTrackingService->transferMoney(
                            $sourceAccount,
                            $businessAccount,
                            $businessShare,
                            'pp_payment',
                            $originalInvoice,
                            $item,
                            "PP Payment - Business share: {$item->name}"
                        );
                    }
                }
            }
        } else {
            // Business item - transfer to business account
            $businessAccount = $moneyTrackingService->getOrCreateBusinessAccount($business);
            if ($sourceAccount && $businessAccount) {
                $moneyTrackingService->transferMoney(
                    $sourceAccount,
                    $businessAccount,
                    $amount,
                    'pp_payment',
                    $originalInvoice,
                    $item,
                    "PP Payment: {$item->name}"
                );
            }
        }
        
        // Mark service delivery queue items as completed if they exist
        $serviceDeliveryQueues = \App\Models\ServiceDeliveryQueue::where('invoice_id', $originalInvoice->id)
            ->where('item_id', $item->id)
            ->where('status', '!=', 'completed')
            ->get();
            
        foreach ($serviceDeliveryQueues as $queue) {
            $queue->markAsCompleted(auth()->id());
        }
    }




}
