<?php

namespace App\Services;

// Version: 2025-09-20-20:30 - Package functionality with comprehensive logging

use App\Models\MoneyAccount;
use App\Models\MoneyTransfer;
use App\Models\PackageTracking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Business;
use App\Models\ContractorProfile;
use App\Models\BalanceHistory;
use App\Models\BusinessBalanceHistory;
use App\Models\ContractorBalanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MoneyTrackingService
{
    /**
     * Initialize money accounts for a business
     */
    public function initializeBusinessAccounts(Business $business)
    {
        $accounts = [
            [
                'name' => 'Package Suspense Account',
                'type' => 'package_suspense_account',
                'description' => 'Holds funds for paid package items if nothing has been used'
            ],
            [
                'name' => 'General Suspense Account',
                'type' => 'general_suspense_account',
                'description' => 'Holds funds for Ordered items not yet offered for all clients'
            ],
            [
                'name' => 'Kashtre Suspense Account',
                'type' => 'kashtre_suspense_account',
                'description' => 'Holds all service fees charged on the invoice'
            ],
            [
                'name' => 'Business Account',
                'type' => 'business_account',
                'description' => 'Holds business funds for sales that have been made'
            ],
            [
                'name' => 'Kashtre Account',
                'type' => 'kashtre_account',
                'description' => 'Holds KASHTRE funds'
            ]
        ];

        foreach ($accounts as $accountData) {
            MoneyAccount::firstOrCreate([
                'business_id' => $business->id,
                'type' => $accountData['type']
            ], [
                'name' => $accountData['name'],
                'description' => $accountData['description'],
                'balance' => 0,
                'currency' => 'UGX',
                'is_active' => true
            ]);
        }
    }

    /**
     * Create or get client account
     */
    public function getOrCreateClientAccount(Client $client)
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => $client->business_id,
            'client_id' => $client->id,
            'type' => 'client_account'
        ], [
            'name' => "Client Account - {$client->name}",
            'description' => "Holds all money paid by client {$client->name}",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }

    /**
     * Create or get client suspense account
     */
    public function getOrCreateClientSuspenseAccount(Client $client)
    {
        Log::info("Creating or getting client suspense account", [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'business_id' => $client->business_id
        ]);

        $suspenseAccount = MoneyAccount::firstOrCreate([
            'business_id' => $client->business_id,
            'client_id' => $client->id,
            'type' => 'general_suspense_account'
        ], [
            'name' => "Client Suspense Account - {$client->name}",
            'description' => "Holds client money in suspense until service delivery",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);

        Log::info("Client suspense account result", [
            'client_id' => $client->id,
            'suspense_account_id' => $suspenseAccount->id,
            'suspense_account_balance' => $suspenseAccount->balance,
            'was_created' => $suspenseAccount->wasRecentlyCreated
        ]);

        return $suspenseAccount;
    }

    /**
     * Create or get contractor account
     */
    public function getOrCreateContractorAccount(ContractorProfile $contractor)
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => $contractor->business_id,
            'contractor_profile_id' => $contractor->id,
            'type' => 'contractor_account'
        ], [
            'name' => "Contractor Account - {$contractor->account_name}",
            'description' => "Holds contractor funds for {$contractor->account_name}",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }

    /**
     * Step 1: Payment received - Money goes to client suspense account
     */
    public function processPaymentReceived(Client $client, $amount, $reference, $paymentMethod, $metadata = [])
    {
        try {
            Log::info("=== PROCESSING PAYMENT RECEIVED ===", [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'amount' => $amount,
                'reference' => $reference,
                'payment_method' => $paymentMethod,
                'metadata' => $metadata
            ]);

            DB::beginTransaction();

            $clientSuspenseAccount = $this->getOrCreateClientSuspenseAccount($client);
            
            // Create transfer record
            $transfer = MoneyTransfer::create([
                'business_id' => $client->business_id,
                'from_account_id' => null, // External payment
                'to_account_id' => $clientSuspenseAccount->id,
                'amount' => $amount,
                'currency' => 'UGX',
                'status' => 'completed',
                'transfer_type' => 'payment_received',
                'client_id' => $client->id,
                'reference' => $reference,
                'description' => "Mobile Money",
                'metadata' => $metadata,
                'processed_at' => now()
            ]);

            // Credit client suspense account
            $clientSuspenseAccount->credit($amount);

            Log::info("Client suspense account credited", [
                'client_id' => $client->id,
                'suspense_account_id' => $clientSuspenseAccount->id,
                'amount_credited' => $amount,
                'new_balance' => $clientSuspenseAccount->fresh()->balance
            ]);

            // DO NOT create balance statement immediately - will be created after payment completion
            Log::info("Payment received - balance statement will be created after payment completion", [
                'client_id' => $client->id,
                'amount' => $amount,
                'reference' => $reference,
                'payment_method' => $paymentMethod
            ]);

            DB::commit();
            
            Log::info("Payment received: {$amount} UGX for client {$client->name} (in suspense)", [
                'client_id' => $client->id,
                'reference' => $reference,
                'transfer_id' => $transfer->id,
                'suspense_account_id' => $clientSuspenseAccount->id
            ]);

            return $transfer;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process payment received", [
                'client_id' => $client->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Step 2: Order confirmed - DO NOT create balance statements yet
     * Balance statements should only be created after payment is completed
     * This method now only logs the order confirmation
     */
    public function processOrderConfirmed(Invoice $invoice, $items)
    {
        try {
            $client = $invoice->client;
            $business = $invoice->business;

            Log::info("=== ORDER CONFIRMED - NO BALANCE STATEMENTS CREATED YET ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'business_id' => $business->id,
                'items_count' => count($items),
                'total_amount' => $invoice->total_amount,
                'service_charge' => $invoice->service_charge,
                'note' => 'Balance statements will be created only after payment completion'
            ]);

            // Log each item for tracking
            foreach ($items as $index => $itemData) {
                $itemId = $itemData['item_id'] ?? $itemData['id'] ?? null;
                if (!$itemId) continue;
                
                $item = Item::find($itemId);
                if (!$item) continue;
                
                $quantity = $itemData['quantity'] ?? 1;
                $totalAmount = $itemData['total_amount'] ?? ($item->default_price * $quantity);

                Log::info("Order confirmed item " . ($index + 1), [
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemId,
                    'item_name' => $item->name,
                    'item_type' => $item->type,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'note' => 'Balance statement will be created after payment completion'
                ]);
            }

            // Log service charge if applicable
            if ($invoice->service_charge > 0) {
                Log::info("Order confirmed service charge", [
                    'invoice_id' => $invoice->id,
                    'service_charge' => $invoice->service_charge,
                    'note' => 'Service charge balance statement will be created after payment completion'
                ]);
            }

            Log::info("=== ORDER CONFIRMED - WAITING FOR PAYMENT COMPLETION ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'note' => 'No balance statements created yet - will be created after payment completion'
            ]);

            return [
                'status' => 'order_confirmed',
                'message' => 'Order confirmed - balance statements will be created after payment completion',
                'items_count' => count($items),
                'total_amount' => $invoice->total_amount
            ];

        } catch (Exception $e) {
            Log::error("Failed to process order confirmation", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        // OLD CODE COMMENTED OUT BELOW:
        /*
        try {
            DB::beginTransaction();

            $clientAccount = $this->getOrCreateClientAccount($invoice->client);
            $business = $invoice->business;

            foreach ($items as $itemData) {
                // Handle both 'item_id' and 'id' keys for item identification
                $itemId = $itemData['item_id'] ?? $itemData['id'] ?? null;
                if (!$itemId) {
                    Log::warning("Item data missing item_id/id", ['itemData' => $itemData]);
                    continue;
                }
                
                $item = Item::find($itemId);
                if (!$item) {
                    Log::warning("Item not found", ['itemId' => $itemId]);
                    continue;
                }
                
                $quantity = $itemData['quantity'] ?? 1;
                $totalAmount = $itemData['total_amount'] ?? ($item->price * $quantity);

                // Check if item is active in package tracking
                $packageTracking = PackageTracking::where('client_id', $invoice->client_id)
                    ->where('included_item_id', $item->id)
                    ->where('status', 'active')
                    ->where('valid_until', '>=', now()->toDateString())
                    ->where('remaining_quantity', '>', 0)
                    ->first();

                if ($packageTracking) {
                    // Item is active in package tracking - use package
                    $packageTracking->useQuantity($quantity);
                    
                    // Transfer from client account to package suspense account
                    $packageSuspenseAccount = MoneyAccount::where('business_id', $business->id)
                        ->where('type', 'package_suspense_account')
                        ->first();

                    $this->transferMoney(
                        $clientAccount,
                        $packageSuspenseAccount,
                        $totalAmount,
                        'order_confirmed',
                        $invoice,
                        $item,
                        $item->display_name ?? $item->name
                    );

                } else {
                    // Item not in package - transfer to general suspense account
                    $generalSuspenseAccount = MoneyAccount::where('business_id', $business->id)
                        ->where('type', 'general_suspense_account')
                        ->first();

                    $this->transferMoney(
                        $clientAccount,
                        $generalSuspenseAccount,
                        $totalAmount,
                        'order_confirmed',
                        $invoice,
                        $item,
                        "Order confirmed: {$item->name}"
                    );
                }
            }

            DB::commit();

            Log::info("Order confirmed for invoice {$invoice->invoice_number}", [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'items_count' => count($items)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process order confirmation", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        */
    }

    /**
     * Step 2.5: Payment completed - Create balance statements for client
     * This is called after payment is confirmed to create the actual balance statements
     */
    public function processPaymentCompleted(Invoice $invoice, $items)
    {
        try {
            DB::beginTransaction();

            $client = $invoice->client;
            $business = $invoice->business;
            $debitRecords = [];

            Log::info("=== PAYMENT COMPLETED - CREATING BALANCE STATEMENTS ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'business_id' => $business->id,
                'items_count' => count($items),
                'total_amount' => $invoice->total_amount,
                'service_charge' => $invoice->service_charge,
                'amount_paid' => $invoice->amount_paid,
                'account_balance_adjustment' => $invoice->account_balance_adjustment
            ]);

            // First, create CREDIT record for payment received
            if ($invoice->amount_paid > 0) {
                $paymentMethods = $invoice->payment_methods ?? [];
                $primaryMethod = !empty($paymentMethods) ? $paymentMethods[0] : 'cash';
                
                $creditRecord = BalanceHistory::recordCredit(
                    $client,
                    $invoice->amount_paid,
                    "Mobile Money",
                    $invoice->invoice_number,
                    "Mobile Money",
                    $primaryMethod
                );

                Log::info("Payment received credit created", [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->amount_paid,
                    'payment_method' => $primaryMethod,
                    'balance_history_id' => $creditRecord->id ?? null
                ]);
            }

            // Create CREDIT record for balance adjustment if used
            if ($invoice->account_balance_adjustment > 0) {
                $balanceCreditRecord = BalanceHistory::recordCredit(
                    $client,
                    $invoice->account_balance_adjustment,
                    "Balance Adjustment Used",
                    $invoice->invoice_number,
                    "POS Balance Adjustment"
                );

                Log::info("Balance adjustment credit created", [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->account_balance_adjustment,
                    'balance_history_id' => $balanceCreditRecord->id ?? null
                ]);
            }

            foreach ($items as $index => $itemData) {
                $itemId = $itemData['item_id'] ?? $itemData['id'] ?? null;
                if (!$itemId) continue;
                
                $item = Item::find($itemId);
                if (!$item) continue;
                
                $quantity = $itemData['quantity'] ?? 1;
                $totalAmount = $itemData['total_amount'] ?? ($item->default_price * $quantity);

                // Create debit record for client balance statement
                // Use display_name for packages (avoids showing other_names), fallback to name for regular items
                $itemDisplayName = $item->display_name ?? $item->name;
                $debitRecord = BalanceHistory::recordDebit(
                    $client,
                    $totalAmount,
                    "{$itemDisplayName} (x{$quantity})",
                    $invoice->invoice_number,
                    "{$itemDisplayName} (x{$quantity})"
                );

                $debitRecords[] = [
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'amount' => $totalAmount,
                    'type' => 'item_payment',
                    'status' => 'completed',
                    'balance_history_id' => $debitRecord->id ?? null
                ];

                Log::info("Balance statement created for item " . ($index + 1), [
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemId,
                    'item_name' => $item->name,
                    'item_display_name' => $itemDisplayName,
                    'quantity' => $quantity,
                    'amount' => $totalAmount,
                    'balance_history_id' => $debitRecord->id ?? null,
                    'note' => 'Using display_name to avoid showing other_names for packages'
                ]);
            }

            // Create debit record for service charge if applicable
            if ($invoice->service_charge > 0) {
                $serviceChargeRecord = BalanceHistory::recordDebit(
                    $client,
                    $invoice->service_charge,
                    "Service Fee",
                    $invoice->invoice_number,
                    "Service Fee"
                );

                $debitRecords[] = [
                    'item_name' => 'Service Charge',
                    'quantity' => 1,
                    'amount' => $invoice->service_charge,
                    'type' => 'service_charge_payment',
                    'status' => 'completed',
                    'balance_history_id' => $serviceChargeRecord->id ?? null
                ];

                Log::info("Balance statement created for service charge", [
                    'invoice_id' => $invoice->id,
                    'service_charge' => $invoice->service_charge,
                    'balance_history_id' => $serviceChargeRecord->id ?? null
                ]);
            }

            DB::commit();

            Log::info("=== PAYMENT COMPLETED - BALANCE STATEMENTS CREATED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $client->id,
                'debit_records_count' => count($debitRecords),
                'total_amount' => $invoice->total_amount
            ]);

            // Send electronic receipts to all parties
            try {
                Log::info("=== STARTING ELECTRONIC RECEIPTS PROCESS ===", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id' => $invoice->client_id,
                    'business_id' => $invoice->business_id,
                    'total_amount' => $invoice->total_amount,
                    'service_charge' => $invoice->service_charge,
                    'payment_status' => $invoice->payment_status
                ]);

                $receiptService = new \App\Services\ReceiptService();
                $result = $receiptService->sendElectronicReceipts($invoice);
                
                Log::info("=== ELECTRONIC RECEIPTS PROCESS COMPLETED ===", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'receipt_service_result' => $result ? 'success' : 'failed'
                ]);
            } catch (\Exception $e) {
                Log::error("=== ELECTRONIC RECEIPTS PROCESS FAILED ===", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'error' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw the exception - receipt failure shouldn't stop the payment completion
            }

            return $debitRecords;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process payment completion balance statements", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Step 3: Service delivered - Money moves to final accounts
     */
    public function processServiceDelivered(Invoice $invoice, $itemId, $quantity = 1)
    {
        try {
            DB::beginTransaction();

            $item = Item::find($itemId);
            $business = $invoice->business;
            $client = $invoice->client;

            // Calculate amount based on item price and quantity
            $amount = $item->price * $quantity;

            // Determine source account (package suspense or general suspense)
            $packageSuspenseAccount = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'package_suspense_account')
                ->first();

            $generalSuspenseAccount = MoneyAccount::where('business_id', $business->id)
                ->where('type', 'general_suspense_account')
                ->first();

            $sourceAccount = null;
            $transferDescription = "";

            // Check if item is from package
            $packageTracking = PackageTracking::where('client_id', $client->id)
                ->where('included_item_id', $item->id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->first();

            if ($packageTracking) {
                $sourceAccount = $packageSuspenseAccount;
                $transferDescription = "Package service delivered: {$item->name}";
            } else {
                $sourceAccount = $generalSuspenseAccount;
                $transferDescription = "Service delivered: {$item->name}";
            }

            // Determine destination account based on item ownership
            if ($item->contractor_account_id) {
                // Contractor item
                $contractor = ContractorProfile::find($item->contractor_account_id);
                $destinationAccount = $this->getOrCreateContractorAccount($contractor);
            } else {
                // Business item
                $destinationAccount = MoneyAccount::where('business_id', $business->id)
                    ->where('type', 'business_account')
                    ->first();
            }

            // Transfer money
            $this->transferMoney(
                $sourceAccount,
                $destinationAccount,
                $amount,
                'service_delivered',
                $invoice,
                $item,
                $transferDescription
            );

            DB::commit();

            Log::info("Service delivered", [
                'invoice_id' => $invoice->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'amount' => $amount
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process service delivery", [
                'invoice_id' => $invoice->id,
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process service charge - Show immediate debit to client
     * Money stays in client suspense account, but debit is shown immediately
     * Actual money movement happens later when "save and exit" is clicked
     */
    public function processServiceCharge(Invoice $invoice, $serviceChargeAmount)
    {
        try {
            if ($serviceChargeAmount <= 0) {
                return null;
            }

            $client = $invoice->client;

            // Create debit record for service charge to show client where their money is allocated
            // Money stays in suspense account, but client sees the debit
            $debitRecord = BalanceHistory::recordDebit(
                $client,
                $serviceChargeAmount,
                "Service Charge Allocated",
                $invoice->invoice_number,
                "Service charge allocated for invoice {$invoice->invoice_number}"
            );

            Log::info("Service charge debit recorded for client (money in suspense)", [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'service_charge_amount' => $serviceChargeAmount
            ]);

            return $debitRecord;

        } catch (Exception $e) {
            Log::error("Failed to process service charge debit", [
                'invoice_id' => $invoice->id,
                'service_charge_amount' => $serviceChargeAmount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        // OLD CODE COMMENTED OUT BELOW:
        /*
        try {
            DB::beginTransaction();

            $clientAccount = $this->getOrCreateClientAccount($invoice->client);
            $kashtreSuspenseAccount = MoneyAccount::where('business_id', $invoice->business_id)
                ->where('type', 'kashtre_suspense_account')
                ->first();

            $this->transferMoney(
                $clientAccount,
                $kashtreSuspenseAccount,
                $serviceChargeAmount,
                'service_charge',
                $invoice,
                null,
                "Service charge for invoice {$invoice->invoice_number}"
            );

            DB::commit();

            Log::info("Service charge processed", [
                'invoice_id' => $invoice->id,
                'amount' => $serviceChargeAmount
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process service charge", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        */
    }

    /**
     * Process refund
     */
    public function processRefund(Client $client, $amount, $reason, $approvedBy = null)
    {
        try {
            DB::beginTransaction();

            $clientAccount = $this->getOrCreateClientAccount($client);
            
            // Create transfer record (from client account to external)
            $transfer = MoneyTransfer::create([
                'business_id' => $client->business_id,
                'from_account_id' => $clientAccount->id,
                'to_account_id' => null, // External refund
                'amount' => $amount,
                'currency' => 'UGX',
                'status' => 'completed',
                'transfer_type' => 'refund_approved',
                'client_id' => $client->id,
                'description' => "Refund: {$reason}",
                'metadata' => ['approved_by' => $approvedBy],
                'processed_at' => now()
            ]);

            // Debit client account
            $clientAccount->credit($amount);

            DB::commit();

            Log::info("Refund processed", [
                'client_id' => $client->id,
                'amount' => $amount,
                'reason' => $reason,
                'approved_by' => $approvedBy
            ]);

            return $transfer;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process refund", [
                'client_id' => $client->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Helper method to transfer money between accounts
     */
    private function transferMoney($fromAccount, $toAccount, $amount, $transferType, $invoice = null, $item = null, $description = '')
    {
        Log::info("=== MONEY TRANSFER STARTED ===", [
            'from_account_id' => $fromAccount->id,
            'from_account_name' => $fromAccount->name,
            'from_account_balance_before' => $fromAccount->balance,
            'to_account_id' => $toAccount->id,
            'to_account_name' => $toAccount->name,
            'to_account_balance_before' => $toAccount->balance,
            'amount' => $amount,
            'transfer_type' => $transferType,
            'description' => $description
        ]);

        // Create transfer record
        $transfer = MoneyTransfer::create([
            'business_id' => $fromAccount->business_id,
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $amount,
            'currency' => 'UGX',
            'status' => 'completed',
            'transfer_type' => $transferType,
            'invoice_id' => $invoice ? $invoice->id : null,
            'client_id' => $invoice ? $invoice->client_id : null,
            'item_id' => $item ? $item->id : null,
            'description' => $description,
            'processed_at' => now()
        ]);

        Log::info("Money transfer record created", [
            'transfer_id' => $transfer->id
        ]);

        // Update account balances
        $fromAccount->debit($amount);  // Money goes out of source account
        $toAccount->credit($amount);   // Money comes into destination account

        Log::info("=== MONEY TRANSFER COMPLETED ===", [
            'transfer_id' => $transfer->id,
            'from_account_balance_after' => $fromAccount->fresh()->balance,
            'to_account_balance_after' => $toAccount->fresh()->balance,
            'amount_transferred' => $amount
        ]);

        return $transfer;
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($businessId, $accountType, $clientId = null, $contractorId = null)
    {
        $query = MoneyAccount::where('business_id', $businessId)
            ->where('type', $accountType);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($contractorId) {
            $query->where('contractor_profile_id', $contractorId);
        }

        $account = $query->first();
        return $account ? $account->balance : 0;
    }

    /**
     * Get client account balance
     */
    public function getClientBalance(Client $client)
    {
        $account = $this->getOrCreateClientAccount($client);
        return $account->balance;
    }

    /**
     * Get business account balance
     */
    public function getBusinessBalance(Business $business)
    {
        return $this->getAccountBalance($business->id, 'business_account');
    }

    /**
     * Get contractor account balance
     */
    public function getContractorBalance(ContractorProfile $contractor)
    {
        $account = $this->getOrCreateContractorAccount($contractor);
        return $account->balance;
    }

    /**
     * Process money transfers when service delivery item moves to partially done
     */
    public function processServiceDeliveryMoneyTransfer($serviceDeliveryQueue, $user = null)
    {
        DB::beginTransaction();
        
        try {
            $invoice = $serviceDeliveryQueue->invoice;
            $item = $serviceDeliveryQueue->item;
            $business = Business::find($invoice->business_id);
            
            Log::info("=== PACKAGE ADJUSTMENT MONEY TRANSFER START ===", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_type' => $item->type,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : null,
                'timestamp' => now()->toISOString()
            ]);
            
            // Get the item amount from the service delivery queue record
            $itemAmount = $serviceDeliveryQueue->price * $serviceDeliveryQueue->quantity;
            
            Log::info("Service delivery queue item details", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'queue_price' => $serviceDeliveryQueue->price,
                'queue_quantity' => $serviceDeliveryQueue->quantity,
                'calculated_item_amount' => $itemAmount,
                'invoice_package_adjustment' => $invoice->package_adjustment ?? 0,
                'invoice_subtotal' => $invoice->subtotal ?? 0,
                'invoice_total_amount' => $invoice->total_amount ?? 0
            ]);
            
            // Get service charge for this invoice
            $serviceCharge = $invoice->service_charge ?? 0;
            
            // Get business account
            $businessAccount = $this->getOrCreateBusinessAccount($business);
            
            // Get Kashtre account (business_id = 1)
            $kashtreAccount = $this->getOrCreateKashtreAccount();
            
            // Check if this is a package item and log package adjustment details
            $isPackageItem = $invoice->package_adjustment > 0;
            
            Log::info("Package adjustment analysis", [
                'is_package_item' => $isPackageItem,
                'invoice_package_adjustment' => $invoice->package_adjustment ?? 0,
                'item_amount_from_queue' => $itemAmount,
                'item_amount_for_display' => $isPackageItem ? $this->calculateItemAmount($invoice, $item) : $itemAmount,
                'money_transfer_amount' => $itemAmount,
                'note' => $isPackageItem ? 'Package item: Display shows item amount, money transfer uses full package amount' : 'Regular item: Same amount for display and transfer'
            ]);
            
            // Transfer item amount to business account
            $this->transferMoney(
                $this->getOrCreateGeneralSuspenseAccount($business),
                $businessAccount,
                $itemAmount,
                'service_delivered',
                $invoice,
                $item,
                "Service delivery payment for {$item->name}"
            );
            
            // Record business balance statement
            BusinessBalanceHistory::recordChange(
                $business->id,
                $businessAccount->id,
                $itemAmount,
                'credit',
                "Service delivery payment for {$item->name}",
                'service_delivery_queue',
                $serviceDeliveryQueue->id,
                [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'client_id' => $invoice->client_id
                ],
                $user ? $user->id : null
            );
            
            // Initialize business share amount (will be updated if contractor share exists)
            $businessShare = $itemAmount;
            
            // Transfer service charge to Kashtre account
            if ($serviceCharge > 0) {
                $this->transferMoney(
                    $this->getOrCreateKashtreSuspenseAccount($business),
                    $kashtreAccount,
                    $serviceCharge,
                    'service_charge',
                    $invoice,
                    $item,
                    "Service charge for invoice #{$invoice->invoice_number}"
                );
                
                // Record Kashtre balance statement
                BusinessBalanceHistory::recordChange(
                    1, // Kashtre business_id
                    $kashtreAccount->id,
                    $serviceCharge,
                    'credit',
                    "Service charge for invoice #{$invoice->invoice_number}",
                    'service_delivery_queue',
                    $serviceDeliveryQueue->id,
                    [
                        'invoice_id' => $invoice->id,
                        'item_id' => $item->id,
                        'client_id' => $invoice->client_id,
                        'business_id' => $business->id
                    ],
                    $user ? $user->id : null
                );
            }
            
            // Handle contractor share if item has contractor
            if ($item->contractor_account_id) {
                $contractor = ContractorProfile::find($item->contractor_account_id);
                if ($contractor && $item->hospital_share < 100) {
                    $contractorShare = ($itemAmount * (100 - $item->hospital_share)) / 100;
                    $businessShare = $itemAmount - $contractorShare;
                    
                    // Transfer contractor share to contractor account
                    $contractorAccount = $this->getOrCreateContractorAccount($contractor);
                    
                    $this->transferMoney(
                        $businessAccount,
                        $contractorAccount,
                        $contractorShare,
                        'contractor_share',
                        $invoice,
                        $item,
                        "Contractor share for {$item->name}"
                    );
                    
                    // Record contractor balance statement
                    ContractorBalanceHistory::recordChange(
                        $contractor->id,
                        $contractorAccount->id,
                        $contractorShare,
                        'credit',
                        "Contractor share for {$item->name}",
                        'service_delivery_queue',
                        $serviceDeliveryQueue->id,
                        [
                            'invoice_id' => $invoice->id,
                            'item_id' => $item->id,
                            'client_id' => $invoice->client_id,
                            'business_id' => $business->id
                        ],
                        $user ? $user->id : null
                    );
                    
                    // Record business balance statement for contractor share
                    BusinessBalanceHistory::recordChange(
                        $business->id,
                        $businessAccount->id,
                        $contractorShare,
                        'debit',
                        "Contractor share payment for {$item->name}",
                        'service_delivery_queue',
                        $serviceDeliveryQueue->id,
                        [
                            'invoice_id' => $invoice->id,
                            'item_id' => $item->id,
                            'client_id' => $invoice->client_id,
                            'contractor_id' => $contractor->id
                        ],
                        $user ? $user->id : null
                    );
                    
                    // Update contractor account balance
                    $contractor->increment('account_balance', $contractorShare);
                    
                    // Sync with money account if it exists
                    if ($contractor->moneyAccount) {
                        $contractor->moneyAccount->credit($contractorShare);
                    }
                    
                    // Update business account balance
                    $business->increment('account_balance', $businessShare);
                    
                    // Sync with money account if it exists
                    if ($business->businessMoneyAccount) {
                        $business->businessMoneyAccount->credit($businessShare);
                    }
                }
            }
            
            // Update business account balance (business share amount)
            $business->increment('account_balance', $businessShare);
            
            // Sync with money account if it exists
            if ($business->businessMoneyAccount) {
                $business->businessMoneyAccount->credit($businessShare);
            }
            
            DB::commit();
            
            Log::info("=== PACKAGE ADJUSTMENT MONEY TRANSFER COMPLETED ===", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_type' => $item->type,
                'item_amount_transferred' => $itemAmount,
                'item_amount_for_display' => $isPackageItem ? $this->calculateItemAmount($invoice, $item) : $itemAmount,
                'service_charge' => $serviceCharge,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'is_package_item' => $isPackageItem,
                'package_adjustment_total' => $invoice->package_adjustment ?? 0,
                'timestamp' => now()->toISOString()
            ]);
            
            // Final summary log for easy tracking
            Log::info("=== PACKAGE ADJUSTMENT SUMMARY ===", [
                'action' => 'ITEM_COMPLETED',
                'invoice_number' => $invoice->invoice_number,
                'item_name' => $item->name,
                'is_package_item' => $isPackageItem,
                'money_transferred_to_business' => $itemAmount,
                'display_amount_for_adjustments' => $isPackageItem ? $this->calculateItemAmount($invoice, $item) : $itemAmount,
                'package_adjustment_total_in_invoice' => $invoice->package_adjustment ?? 0,
                'note' => $isPackageItem ? 'Package item: Full package amount transferred, item amount shown in adjustments' : 'Regular item: Same amount for transfer and display',
                'timestamp' => now()->toISOString()
            ]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process service delivery money transfer", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Calculate item amount considering package adjustments
     * For package items: returns the item amount (what customer paid for this specific item)
     * For regular items: returns the original item amount
     */
    private function calculateItemAmount($invoice, $item)
    {
        Log::info("=== CALCULATING ITEM AMOUNT FOR DISPLAY ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_type' => $item->type,
            'invoice_package_adjustment' => $invoice->package_adjustment ?? 0,
            'invoice_subtotal' => $invoice->subtotal ?? 0,
            'timestamp' => now()->toISOString()
        ]);
        
        // Get the original item price from invoice items array
        $invoiceItem = null;
        if ($invoice->items && is_array($invoice->items)) {
            foreach ($invoice->items as $invItem) {
                if (isset($invItem['id']) && $invItem['id'] == $item->id) {
                    $invoiceItem = $invItem;
                    break;
                }
            }
        }
        
        if (!$invoiceItem) {
            Log::warning("Invoice item not found in invoice items array", [
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'invoice_items_count' => is_array($invoice->items) ? count($invoice->items) : 0
            ]);
            return 0;
        }
        
        $originalAmount = ($invoiceItem['quantity'] ?? 0) * ($invoiceItem['price'] ?? 0);
        
        Log::info("Invoice item details found", [
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'invoice_item_quantity' => $invoiceItem['quantity'] ?? 0,
            'invoice_item_price' => $invoiceItem['price'] ?? 0,
            'original_amount' => $originalAmount
        ]);
        
        // For package items, the adjustment should show the item amount (what customer paid)
        // The actual money transfer still uses the full package amount
        if ($invoice->package_adjustment > 0) {
            // For display purposes, return the item amount (what customer paid for this specific item)
            $itemAmount = $this->calculateItemPackageAdjustment($invoice, $item);
            
            Log::info('=== PACKAGE ITEM AMOUNT CALCULATION ===', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'original_amount' => $originalAmount,
                'item_amount_for_display' => $itemAmount,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_adjustment_total' => $invoice->package_adjustment,
                'note' => 'PACKAGE ITEM: Display shows item amount, money transfer uses full package amount'
            ]);
            
            return $itemAmount;
        }
        
        Log::info('=== REGULAR ITEM AMOUNT CALCULATION ===', [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'original_amount' => $originalAmount,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'note' => 'REGULAR ITEM: Same amount for display and transfer'
        ]);
        
        return max(0, $originalAmount);
    }
    
    /**
     * Calculate package adjustment for a specific item
     * For display purposes, this should return the item amount (what customer paid for this specific item)
     * The actual money transfer still uses the full package amount
     */
    private function calculateItemPackageAdjustment($invoice, $item)
    {
        Log::info("=== CALCULATING PACKAGE ADJUSTMENT FOR ITEM ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_type' => $item->type,
            'invoice_package_adjustment_total' => $invoice->package_adjustment ?? 0,
            'timestamp' => now()->toISOString()
        ]);
        
        // Get the item from invoice items array
        $invoiceItem = null;
        if ($invoice->items && is_array($invoice->items)) {
            foreach ($invoice->items as $invItem) {
                if (isset($invItem['id']) && $invItem['id'] == $item->id) {
                    $invoiceItem = $invItem;
                    break;
                }
            }
        }
        
        if (!$invoiceItem) {
            Log::warning("Invoice item not found for package adjustment calculation", [
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'invoice_items_count' => is_array($invoice->items) ? count($invoice->items) : 0
            ]);
            return 0;
        }
        
        // Return the actual item amount (what the customer paid for this specific item)
        // This is what should be displayed in package adjustments
        $itemAmount = ($invoiceItem['quantity'] ?? 0) * ($invoiceItem['price'] ?? 0);
        
        Log::info('=== PACKAGE ADJUSTMENT CALCULATION RESULT ===', [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'invoice_item_quantity' => $invoiceItem['quantity'] ?? 0,
            'invoice_item_price' => $invoiceItem['price'] ?? 0,
            'calculated_item_amount' => $itemAmount,
            'invoice_package_adjustment_total' => $invoice->package_adjustment ?? 0,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'note' => 'DISPLAY AMOUNT: Shows item amount (what customer paid), money transfer uses full package amount'
        ]);
        
        return $itemAmount;
    }
    
    /**
     * Get or create business account
     */
    public function getOrCreateBusinessAccount(Business $business)
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => $business->id,
            'type' => 'business_account'
        ], [
            'name' => "Business Account - {$business->name}",
            'description' => "Business account for {$business->name}",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }
    
    /**
     * Get or create Kashtre account
     */
    public function getOrCreateKashtreAccount()
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => 1, // Kashtre business_id
            'type' => 'kashtre_account'
        ], [
            'name' => "Kashtre Account",
            'description' => "Kashtre platform account",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }
    
    /**
     * Get or create general suspense account
     */
    private function getOrCreateGeneralSuspenseAccount(Business $business)
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => $business->id,
            'type' => 'general_suspense_account'
        ], [
            'name' => "General Suspense Account - {$business->name}",
            'description' => "General suspense account for {$business->name}",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }
    
    /**
     * Get or create Kashtre suspense account
     */
    private function getOrCreateKashtreSuspenseAccount(Business $business)
    {
        return MoneyAccount::firstOrCreate([
            'business_id' => $business->id,
            'type' => 'kashtre_suspense_account'
        ], [
            'name' => "Kashtre Suspense Account - {$business->name}",
            'description' => "Kashtre suspense account for {$business->name}",
            'balance' => 0,
            'currency' => 'UGX',
            'is_active' => true
        ]);
    }

    /**
     * Process "Save and Exit" - Move money from client suspense account to final accounts
     * This is called when the user clicks "save and exit" to finalize the order
     */
    public function processSaveAndExit(Invoice $invoice, $items, $itemStatus = null)
    {
        try {
            Log::info("=== SAVE AND EXIT PROCESSING STARTED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'client_name' => $invoice->client->name ?? 'Unknown',
                'business_id' => $invoice->business_id,
                'business_name' => $invoice->business->name ?? 'Unknown',
                'branch_id' => $invoice->branch_id,
                'items_count' => count($items),
                'service_charge' => $invoice->service_charge ?? 0,
                'package_adjustment' => $invoice->package_adjustment ?? 0,
                'item_status' => $itemStatus,
                'total_amount' => $invoice->total_amount ?? 0,
                'subtotal_1' => $invoice->subtotal_1 ?? 0,
                'subtotal_2' => $invoice->subtotal_2 ?? 0,
                'invoice_status' => $invoice->status,
                'timestamp' => now()->toDateTimeString()
            ]);

            Log::info("Items being processed in Save & Exit", [
                'invoice_id' => $invoice->id,
                'items' => $items
            ]);

            DB::beginTransaction();

            $client = $invoice->client;
            $business = $invoice->business;
            $clientSuspenseAccount = $this->getOrCreateClientSuspenseAccount($client);
            $transferRecords = [];

            Log::info("Client suspense account details", [
                'client_id' => $client->id,
                'suspense_account_id' => $clientSuspenseAccount->id,
                'suspense_account_balance_before' => $clientSuspenseAccount->balance
            ]);

            foreach ($items as $index => $itemData) {
                $itemId = $itemData['item_id'] ?? $itemData['id'] ?? null;
                if (!$itemId) {
                    Log::warning("Item ID not found in item data", [
                        'item_index' => $index,
                        'item_data' => $itemData
                    ]);
                    continue;
                }
                
                $item = Item::find($itemId);
                if (!$item) {
                    Log::warning("Item not found", [
                        'item_id' => $itemId,
                        'item_index' => $index
                    ]);
                    continue;
                }
                
                $quantity = $itemData['quantity'] ?? 1;
                $totalAmount = $itemData['total_amount'] ?? ($item->default_price * $quantity);

                Log::info("Processing item " . ($index + 1), [
                    'item_id' => $itemId,
                    'item_name' => $item->name,
                    'item_type' => $item->type,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'contractor_account_id' => $item->contractor_account_id
                ]);

                // Check if this item is included in a package AND if package adjustment is being processed
                $isIncludedInPackage = $item->includedInPackages()->exists();
                $hasPackageAdjustment = $invoice->package_adjustment > 0;
                
                Log::info("Package item check for processing decision", [
                    'item_id' => $itemId,
                    'item_name' => $item->name,
                    'is_included_in_package' => $isIncludedInPackage,
                    'has_package_adjustment' => $hasPackageAdjustment,
                    'package_adjustment_amount' => $invoice->package_adjustment,
                    'invoice_id' => $invoice->id
                ]);
                
                if ($isIncludedInPackage && $hasPackageAdjustment) {
                    // If item is included in package AND there's a package adjustment,
                    // skip individual processing - package adjustment will handle the money movement
                    Log::info("Item is included in package with package adjustment - skipping individual processing", [
                        'item_id' => $itemId,
                        'item_name' => $item->name,
                        'item_type' => $item->type,
                        'total_amount' => $totalAmount,
                        'package_adjustment' => $invoice->package_adjustment,
                        'reason' => 'Package item with package adjustment - will be processed via package adjustment logic'
                    ]);
                    // Skip individual item processing - package adjustment will handle the money movement
                    continue;
                } else if ($isIncludedInPackage && !$hasPackageAdjustment) {
                    Log::info("Item is included in package but no package adjustment - processing individually", [
                        'item_id' => $itemId,
                        'item_name' => $item->name,
                        'item_type' => $item->type,
                        'total_amount' => $totalAmount,
                        'reason' => 'No package adjustment for this invoice - processing as individual item'
                    ]);
                }
                
                // Handle bulk items differently
                if ($item->type === 'bulk') {
                    Log::info("Processing bulk item", [
                        'bulk_item_id' => $itemId,
                        'bulk_item_name' => $item->name,
                        'bulk_total_amount' => $totalAmount
                    ]);
                    
                    // For bulk items, we process the entire bulk amount
                    // The included items will be handled separately for service point distribution
                    $this->processBulkItem($item, $totalAmount, $clientSuspenseAccount, $business, $invoice, $transferRecords);
                } else {
                    // Handle regular items (good, service) as before
                    $this->processRegularItem($item, $totalAmount, $clientSuspenseAccount, $business, $invoice, $transferRecords, $quantity);
                }
            }

            // Handle package adjustment money movement (only once per invoice and when package items are used)
            Log::info("=== PACKAGE ADJUSTMENT CHECK START ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_adjustment' => $invoice->package_adjustment,
                'item_status' => $itemStatus,
                'service_delivery_queue_id' => null, // Not available in processSaveAndExit context
                'item_id' => null, // Not available in processSaveAndExit context
                'item_name' => null, // Not available in processSaveAndExit context
                'client_id' => $invoice->client_id,
                'client_name' => $invoice->client_name,
                'business_id' => $invoice->business_id,
                'timestamp' => now()->toISOString()
            ]);

            if ($invoice->package_adjustment > 0 && in_array($itemStatus, ['completed', 'partially_done'])) {
                Log::info("Package adjustment conditions met - checking for existing transfers", [
                    'invoice_id' => $invoice->id,
                    'package_adjustment' => $invoice->package_adjustment,
                    'item_status' => $itemStatus
                ]);

                // Check if package adjustment has already been processed for this invoice
                $existingPackageAdjustmentTransfer = \App\Models\MoneyTransfer::where('invoice_id', $invoice->id)
                    ->where('description', 'like', '%Package adjustment for invoice%')
                    ->first();
                
                Log::info("Package adjustment transfer check result", [
                    'invoice_id' => $invoice->id,
                    'existing_transfer_found' => $existingPackageAdjustmentTransfer ? true : false,
                    'existing_transfer_id' => $existingPackageAdjustmentTransfer->id ?? null,
                    'existing_transfer_description' => $existingPackageAdjustmentTransfer->description ?? null
                ]);
                
                if ($existingPackageAdjustmentTransfer) {
                    Log::info("Package adjustment already processed for this invoice - skipping", [
                        'invoice_id' => $invoice->id,
                        'existing_transfer_id' => $existingPackageAdjustmentTransfer->id,
                        'package_adjustment_amount' => $invoice->package_adjustment,
                        'reason' => 'Package adjustment transfer record already exists for this invoice'
                    ]);
                } else {
                // Get detailed item information for logging (using first item from invoice since serviceDeliveryQueue is not available)
                $firstItem = $invoice->items[0] ?? null;
                $itemModel = $firstItem ? \App\Models\Item::find($firstItem['id'] ?? $firstItem['item_id']) : null;
                $itemType = $itemModel ? $itemModel->type : 'unknown';
                $isPackageItem = $itemModel && $itemModel->type === 'package';
                $isIncludedInPackage = $itemModel ? $itemModel->includedInPackages()->exists() : false;
                
                Log::info("Processing package adjustment money movement", [
                    'package_adjustment_amount' => $invoice->package_adjustment,
                    'invoice_id' => $invoice->id,
                    'item_status' => $itemStatus,
                    'triggering_item_id' => $firstItem['id'] ?? $firstItem['item_id'] ?? null,
                    'triggering_item_name' => $firstItem['name'] ?? null,
                    'triggering_item_type' => $itemType,
                    'is_package_item' => $isPackageItem,
                    'is_included_in_package' => $isIncludedInPackage,
                    'service_delivery_queue_id' => null, // Not available in processSaveAndExit context
                    'reason' => 'Package items are being used - moving package adjustment money to business account',
                    'timestamp' => now()->toISOString()
                ]);
                    
                    // Move package adjustment money from client suspense to business account
                    $this->processPackageAdjustmentMoneyMovement($invoice, $clientSuspenseAccount, $business, $transferRecords);
                    
                    // Update package tracking records and create package sales
                    $this->updatePackageTrackingForAdjustments($invoice, $items);
                    
                    // Create package sales records and account statements
                    $this->createPackageSalesAndStatements($invoice, $items);
                }
            } else {
                Log::info("Package adjustment conditions NOT met", [
                    'invoice_id' => $invoice->id,
                    'package_adjustment' => $invoice->package_adjustment,
                    'item_status' => $itemStatus,
                    'reason' => $invoice->package_adjustment <= 0 ? 'No package adjustment' : 'Item status not completed/partially_done'
                ]);
            }

            Log::info("=== PACKAGE ADJUSTMENT CHECK END ===", [
                'invoice_id' => $invoice->id
            ]);

            // Handle service charge if applicable (only once per invoice and when item is completed or partially done)
            $shouldProcessServiceCharge = false;
            if ($invoice->service_charge > 0 && in_array($itemStatus, ['completed', 'partially_done'])) {
                // Check if service charge has already been processed for this invoice (any transfer type)
                $existingServiceChargeTransfer = \App\Models\MoneyTransfer::where('invoice_id', $invoice->id)
                    ->where('description', 'like', '%Service charge for invoice%')
                    ->first();
                
                if ($existingServiceChargeTransfer) {
                    // Service charge has already been processed for this invoice - check if money was actually moved
                    Log::info("Service charge already processed for this invoice - checking if money was actually moved", [
                        'invoice_id' => $invoice->id,
                        'existing_transfer_id' => $existingServiceChargeTransfer->id,
                        'service_charge_amount' => $invoice->service_charge,
                        'transfer_status' => $existingServiceChargeTransfer->status,
                        'transfer_amount' => $existingServiceChargeTransfer->amount,
                        'from_account_id' => $existingServiceChargeTransfer->from_account_id,
                        'to_account_id' => $existingServiceChargeTransfer->to_account_id,
                        'reason' => 'Service charge transfer record already exists for this invoice'
                    ]);
                    
                    // Check if the transfer actually moved money by looking at account balances
                    $fromAccount = \App\Models\MoneyAccount::find($existingServiceChargeTransfer->from_account_id);
                    $toAccount = \App\Models\MoneyAccount::find($existingServiceChargeTransfer->to_account_id);
                    
                    Log::info("Service charge transfer account details", [
                        'transfer_id' => $existingServiceChargeTransfer->id,
                        'from_account_id' => $existingServiceChargeTransfer->from_account_id,
                        'from_account_name' => $fromAccount ? $fromAccount->name : 'NOT FOUND',
                        'from_account_balance' => $fromAccount ? $fromAccount->balance : 'N/A',
                        'to_account_id' => $existingServiceChargeTransfer->to_account_id,
                        'to_account_name' => $toAccount ? $toAccount->name : 'NOT FOUND',
                        'to_account_balance' => $toAccount ? $toAccount->balance : 'N/A',
                        'transfer_amount' => $existingServiceChargeTransfer->amount
                    ]);
                    
                    $shouldProcessServiceCharge = false;
                } else {
                    Log::info("Processing service charge for item", [
                        'service_charge_amount' => $invoice->service_charge,
                        'invoice_id' => $invoice->id,
                        'item_status' => $itemStatus
                    ]);
                    $shouldProcessServiceCharge = true;
                }
            }

                
                if ($shouldProcessServiceCharge) {

                $kashtreSuspenseAccount = $this->getOrCreateKashtreSuspenseAccount($business);
                
                Log::info("Kashtre suspense account details", [
                    'kashtre_account_id' => $kashtreSuspenseAccount->id,
                    'kashtre_account_balance_before' => $kashtreSuspenseAccount->balance
                ]);
                
                $transfer = $this->transferMoney(
                    $clientSuspenseAccount,
                    $kashtreSuspenseAccount,
                    $invoice->service_charge,
                    'save_and_exit',
                    $invoice,
                    null,
                    "Service charge for invoice {$invoice->invoice_number}"
                );

                Log::info("Service charge transfer completed", [
                    'transfer_id' => $transfer->id,
                    'kashtre_account_balance_after' => $kashtreSuspenseAccount->fresh()->balance
                ]);

                // Create balance statement for Kashtre account
                Log::info("Creating Kashtre balance statement", [
                    'business_id' => $business->id,
                    'amount' => $invoice->service_charge,
                    'type' => 'credit'
                ]);

                BusinessBalanceHistory::recordChange(
                    1, // Kashtre business_id
                    $kashtreSuspenseAccount->id,
                    $invoice->service_charge,
                    'credit',
                    "Service charge received",
                    'invoice',
                    $invoice->id,
                    [
                        'invoice_number' => $invoice->invoice_number,
                        'description' => "Service delivery completed - Service charge for invoice"
                    ]
                );

                $transferRecords[] = [
                    'item_name' => 'Service Charge',
                    'amount' => $invoice->service_charge,
                    'destination' => $kashtreSuspenseAccount->name,
                    'transfer_id' => $transfer->id
                ];
            }

            DB::commit();

            Log::info("=== SAVE AND EXIT PROCESSING COMPLETED SUCCESSFULLY ===", [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'transfer_records_count' => count($transferRecords),
                'client_suspense_balance_final' => $clientSuspenseAccount->fresh()->balance,
                'total_amount_processed' => array_sum(array_column($transferRecords, 'amount'))
            ]);

            return $transferRecords;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process save and exit", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process a bulk item - move entire bulk amount when any included item is processed
     */
    private function processBulkItem($bulkItem, $totalAmount, $clientSuspenseAccount, $business, $invoice, &$transferRecords)
    {
        Log::info("=== PROCESSING BULK ITEM ===", [
            'bulk_item_id' => $bulkItem->id,
            'bulk_item_name' => $bulkItem->name,
            'bulk_total_amount' => $totalAmount
        ]);

        // Get all included items in this bulk
        $includedItems = \App\Models\BulkItem::where('bulk_item_id', $bulkItem->id)
            ->with('includedItem')
            ->get();

        Log::info("Bulk item contains included items", [
            'bulk_item_id' => $bulkItem->id,
            'included_items_count' => $includedItems->count(),
            'included_items' => $includedItems->pluck('includedItem.name')->toArray()
        ]);

        // Determine destination account based on bulk item type
        if ($bulkItem->contractor_account_id) {
            // Contractor bulk item - move to contractor account
            $contractor = ContractorProfile::find($bulkItem->contractor_account_id);
            $destinationAccount = $this->getOrCreateContractorAccount($contractor);
            $transferDescription = "Contractor payment for bulk: {$bulkItem->name}";
            
            Log::info("Bulk item assigned to contractor", [
                'bulk_item_id' => $bulkItem->id,
                'contractor_id' => $contractor->id,
                'contractor_name' => $contractor->name,
                'destination_account_id' => $destinationAccount->id,
                'destination_account_balance_before' => $destinationAccount->balance
            ]);
        } else {
            // Business bulk item - move to business account
            $destinationAccount = $this->getOrCreateBusinessAccount($business);
            $transferDescription = "Business payment for bulk: {$bulkItem->name}";
            
            Log::info("Bulk item assigned to business", [
                'bulk_item_id' => $bulkItem->id,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'destination_account_id' => $destinationAccount->id,
                'destination_account_balance_before' => $destinationAccount->balance
            ]);
        }

        // Transfer entire bulk amount from client suspense to destination account
        Log::info("Initiating bulk money transfer", [
            'from_account_id' => $clientSuspenseAccount->id,
            'to_account_id' => $destinationAccount->id,
            'amount' => $totalAmount,
            'description' => $transferDescription
        ]);

        $transfer = $this->transferMoney(
            $clientSuspenseAccount,
            $destinationAccount,
            $totalAmount,
            'save_and_exit',
            $invoice,
            $bulkItem,
            $transferDescription
        );

        Log::info("Bulk money transfer completed", [
            'transfer_id' => $transfer->id,
            'from_account_balance_after' => $clientSuspenseAccount->fresh()->balance,
            'to_account_balance_after' => $destinationAccount->fresh()->balance
        ]);

        // Create balance statement for the destination account
        if ($bulkItem->contractor_account_id) {
            Log::info("Creating contractor balance statement for bulk", [
                'contractor_id' => $contractor->id,
                'amount' => $totalAmount,
                'type' => 'credit'
            ]);
            
            ContractorBalanceHistory::recordChange(
                $contractor->id,
                $destinationAccount->id,
                $totalAmount,
                'credit',
                "{$bulkItem->name}",
                'invoice',
                $invoice->id,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'bulk_item_name' => $bulkItem->name,
                    'description' => "Service delivery completed - Bulk: {$bulkItem->name}"
                ]
            );
        } else {
            Log::info("Creating business balance statement for bulk", [
                'business_id' => $business->id,
                'amount' => $totalAmount,
                'type' => 'credit'
            ]);
            
            BusinessBalanceHistory::recordChange(
                $business->id,
                $destinationAccount->id,
                $totalAmount,
                'credit',
                "{$bulkItem->name}",
                'invoice',
                $invoice->id,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'bulk_item_name' => $bulkItem->name,
                    'description' => "Service delivery completed - Bulk: {$bulkItem->name}"
                ]
            );
        }

        $transferRecords[] = [
            'item_name' => "Bulk: {$bulkItem->name}",
            'amount' => $totalAmount,
            'destination' => $destinationAccount->name,
            'transfer_id' => $transfer->id
        ];

        Log::info("=== BULK ITEM PROCESSING COMPLETED ===", [
            'bulk_item_id' => $bulkItem->id,
            'bulk_total_amount' => $totalAmount,
            'included_items_count' => $includedItems->count()
        ]);
    }

    /**
     * Process a regular item (good, service) - standard money movement
     */
    private function processRegularItem($item, $totalAmount, $clientSuspenseAccount, $business, $invoice, &$transferRecords, $quantity)
    {
        Log::info("=== PROCESSING REGULAR ITEM ===", [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_type' => $item->type,
            'total_amount' => $totalAmount
        ]);

        // Determine destination account based on item type
        if ($item->contractor_account_id) {
            // Contractor item - move to contractor account
            $contractor = ContractorProfile::find($item->contractor_account_id);
            $destinationAccount = $this->getOrCreateContractorAccount($contractor);
            $transferDescription = "{$item->name} ({$quantity})";
            
            Log::info("Item assigned to contractor", [
                'item_id' => $item->id,
                'contractor_id' => $contractor->id,
                'contractor_name' => $contractor->name,
                'destination_account_id' => $destinationAccount->id,
                'destination_account_balance_before' => $destinationAccount->balance
            ]);
        } else {
            // Business item - move to business account
            $destinationAccount = $this->getOrCreateBusinessAccount($business);
            $transferDescription = "{$item->name} ({$quantity})";
            
            Log::info("Item assigned to business", [
                'item_id' => $item->id,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'destination_account_id' => $destinationAccount->id,
                'destination_account_balance_before' => $destinationAccount->balance
            ]);
        }

        // Transfer money from client suspense to destination account
        Log::info("Initiating money transfer", [
            'from_account_id' => $clientSuspenseAccount->id,
            'to_account_id' => $destinationAccount->id,
            'amount' => $totalAmount,
            'description' => $transferDescription
        ]);

        $transfer = $this->transferMoney(
            $clientSuspenseAccount,
            $destinationAccount,
            $totalAmount,
            'save_and_exit',
            $invoice,
            $item,
            $transferDescription
        );

        Log::info("Money transfer completed", [
            'transfer_id' => $transfer->id,
            'from_account_balance_after' => $clientSuspenseAccount->fresh()->balance,
            'to_account_balance_after' => $destinationAccount->fresh()->balance
        ]);

        // Create balance statement for the destination account
        if ($item->contractor_account_id) {
            Log::info("Creating contractor balance statement", [
                'contractor_id' => $contractor->id,
                'amount' => $totalAmount,
                'type' => 'credit'
            ]);
            
            ContractorBalanceHistory::recordChange(
                $contractor->id,
                $destinationAccount->id,
                $totalAmount,
                'credit',
                "{$item->name}",
                'invoice',
                $invoice->id,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'item_name' => $item->name,
                    'description' => "Service delivery completed - Item: {$item->name}"
                ]
            );
        } else {
            Log::info("Creating business balance statement", [
                'business_id' => $business->id,
                'amount' => $totalAmount,
                'type' => 'credit'
            ]);
            
            BusinessBalanceHistory::recordChange(
                $business->id,
                $destinationAccount->id,
                $totalAmount,
                'credit',
                "{$item->name}",
                'invoice',
                $invoice->id,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'item_name' => $item->name,
                    'description' => "Service delivery completed - Item: {$item->name}"
                ]
            );
        }

        $transferRecords[] = [
            'item_name' => $item->name,
            'amount' => $totalAmount,
            'destination' => $destinationAccount->name,
            'transfer_id' => $transfer->id
        ];

        Log::info("=== REGULAR ITEM PROCESSING COMPLETED ===", [
            'item_id' => $item->id,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Check if an item is covered by the current package adjustment
     * This helps determine if we should skip individual processing for package items
     */
    private function isItemCoveredByPackageAdjustment($item, $invoice)
    {
        Log::info("=== CHECKING IF ITEM IS COVERED BY PACKAGE ADJUSTMENT ===", [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'invoice_id' => $invoice->id,
            'invoice_package_adjustment' => $invoice->package_adjustment,
            'client_id' => $invoice->client_id,
            'business_id' => $invoice->business_id
        ]);
        
        // Get client's valid package tracking records
        $validPackages = \App\Models\PackageTracking::where('client_id', $invoice->client_id)
            ->where('business_id', $invoice->business_id)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->where('valid_until', '>=', now()->toDateString())
            ->with(['packageItem.packageItems.includedItem'])
            ->get();
            
        Log::info("Found valid packages for coverage check", [
            'item_id' => $item->id,
            'valid_packages_count' => $validPackages->count(),
            'valid_packages' => $validPackages->map(function($pkg) {
                return [
                    'id' => $pkg->id,
                    'package_price' => $pkg->package_price,
                    'remaining_quantity' => $pkg->remaining_quantity,
                    'status' => $pkg->status
                ];
            })
        ]);

        foreach ($validPackages as $packageTracking) {
            // Check if the current item is included in this package
            $packageItems = $packageTracking->packageItem->packageItems;
            
            foreach ($packageItems as $packageItem) {
                if ($packageItem->included_item_id == $item->id) {
                    // This item is included in a valid package
                    // Check if the package adjustment amount matches this package's price
                    $packagePrice = $packageTracking->package_price;
                    
                    Log::info("Checking if item is covered by package adjustment", [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'package_tracking_id' => $packageTracking->id,
                        'package_price' => $packagePrice,
                        'invoice_package_adjustment' => $invoice->package_adjustment,
                        'is_covered' => $invoice->package_adjustment >= $packagePrice
                    ]);
                    
                    // If the package adjustment amount is at least the package price, 
                    // then this item is covered by the package adjustment
                    return $invoice->package_adjustment >= $packagePrice;
                }
            }
        }
        
        return false;
    }

    /**
     * Get the actual package amount for an invoice
     * This is needed because invoice.package_adjustment contains the item amount,
     * but for money movement we need the actual package amount
     */
    private function getPackageAmountForInvoice($invoice)
    {
        // Get client's valid package tracking records
        $validPackages = \App\Models\PackageTracking::where('client_id', $invoice->client_id)
            ->where('business_id', $invoice->business_id)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->where('valid_until', '>=', now()->toDateString())
            ->get();
            
        Log::info("Getting package amount for invoice", [
            'invoice_id' => $invoice->id,
            'invoice_package_adjustment' => $invoice->package_adjustment,
            'valid_packages_count' => $validPackages->count(),
            'valid_packages' => $validPackages->map(function($pkg) {
                return [
                    'id' => $pkg->id,
                    'package_price' => $pkg->package_price,
                    'remaining_quantity' => $pkg->remaining_quantity,
                    'status' => $pkg->status
                ];
            })
        ]);

        // Return the package price from the first valid package
        // In most cases, there should be only one active package per client
        if ($validPackages->count() > 0) {
            $packageAmount = $validPackages->first()->package_price;
            Log::info("Package amount determined", [
                'invoice_id' => $invoice->id,
                'package_amount' => $packageAmount,
                'invoice_package_adjustment' => $invoice->package_adjustment,
                'note' => 'Using package amount for money movement, not item amount'
            ]);
            return $packageAmount;
        }
        
        // Fallback to invoice package adjustment if no valid packages found
        Log::warning("No valid packages found, using invoice package adjustment as fallback", [
            'invoice_id' => $invoice->id,
            'fallback_amount' => $invoice->package_adjustment
        ]);
        return $invoice->package_adjustment;
    }

    /**
     * Get package information for invoice descriptions
     */
    private function getPackageInfoForInvoice($invoice)
    {
        try {
            // Get client's valid package tracking records
            $validPackages = \App\Models\PackageTracking::where('client_id', $invoice->client_id)
                ->where('business_id', $invoice->business_id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->where('valid_until', '>=', now()->toDateString())
                ->with(['packageItem.packageItems.includedItem'])
                ->get();

            $packageDescriptions = [];
            $packageTrackingNumbers = [];
            
            foreach ($validPackages as $packageTracking) {
                if ($packageTracking->tracking_number) {
                    $packageName = $packageTracking->packageItem->display_name ?? 'Unknown Package';
                    $trackingNumber = $packageTracking->tracking_number; // Use the actual tracking number from database (format: PKG-X-YmdHis)
                    $packageDescriptions[] = "{$packageName} (Ref: {$trackingNumber})";
                    $packageTrackingNumbers[] = $trackingNumber;
                }
            }
            
            // Simplify description - use first package, add "and X more" if multiple
            if (count($packageDescriptions) == 1) {
                $description = $packageDescriptions[0];
            } else {
                $description = $packageDescriptions[0] . " and " . (count($packageDescriptions) - 1) . " more";
            }
            $trackingNumbers = implode(', ', array_unique($packageTrackingNumbers));
            
            return [
                'description' => $description ?: 'Package items',
                'tracking_numbers' => $trackingNumbers ?: 'N/A'
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get package info for invoice", [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id
            ]);
            
            return [
                'description' => 'Package items',
                'tracking_numbers' => 'N/A'
            ];
        }
    }

    /**
     * Process package adjustment money movement from client suspense to business account
     * This is called only when package items are actually used (Save & Exit)
     */
    private function processPackageAdjustmentMoneyMovement($invoice, $clientSuspenseAccount, $business, &$transferRecords)
    {
        Log::info("=== PACKAGE ADJUSTMENT MONEY MOVEMENT START ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_id' => $invoice->client_id,
            'business_id' => $business->id,
            'business_name' => $business->name,
            'description_format_update' => 'Updated package descriptions: Business=Package name + ref, Client=Item + quantity + ref',
            'amount_calculation_update' => 'Business statement now uses sum of item amounts (actual revenue) instead of package price',
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Check if this invoice already has individual package item entries to prevent duplicates
        $hasIndividualPackageEntries = \App\Models\BalanceHistory::where('client_id', $invoice->client_id)
            ->where('reference_number', $invoice->invoice_number)
            ->where('transaction_type', 'debit')
            ->where('description', 'like', '%(x%')
            ->exists();

        if ($hasIndividualPackageEntries) {
            Log::info("Skipping package purchase entry - individual package items already recorded", [
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'reason' => 'Individual package items already recorded as debits - avoiding duplicate package purchase entry'
            ]);
            return; // Skip creating package purchase entry
        }

        // For package items, we need to use the actual package amount, not the item amount
        // The invoice package_adjustment contains the item amount (113), but we need the package amount (120)
        $packageAdjustmentAmount = $this->getPackageAmountForInvoice($invoice);
        
        Log::info("=== PROCESSING PACKAGE ADJUSTMENT MONEY MOVEMENT START ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'package_adjustment_amount' => $packageAdjustmentAmount,
            'client_suspense_account_id' => $clientSuspenseAccount->id,
            'client_suspense_account_name' => $clientSuspenseAccount->name,
            'client_suspense_balance_before' => $clientSuspenseAccount->balance,
            'business_id' => $business->id,
            'business_name' => $business->name
        ]);

        // Get business account
        $businessAccount = $this->getOrCreateBusinessAccount($business);
        
        Log::info("Business account details", [
            'business_account_id' => $businessAccount->id,
            'business_account_name' => $businessAccount->name,
            'business_account_balance_before' => $businessAccount->balance
        ]);
        
        // Create transfer record from client suspense to business account
        Log::info("Creating package adjustment transfer record", [
            'from_account_id' => $clientSuspenseAccount->id,
            'to_account_id' => $businessAccount->id,
            'amount' => $packageAdjustmentAmount,
            'invoice_id' => $invoice->id
        ]);

        // Get package information for description
        $packageInfo = $this->getPackageInfoForInvoice($invoice);
        
        $transfer = MoneyTransfer::create([
            'business_id' => $business->id,
            'from_account_id' => $clientSuspenseAccount->id,
            'to_account_id' => $businessAccount->id,
            'amount' => $packageAdjustmentAmount,
            'type' => 'package_adjustment_transfer',
            'description' => $packageInfo['description'],
            'reference' => $invoice->invoice_number,
            'invoice_id' => $invoice->id,
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'description' => 'Package adjustment money moved to business account when package items were used',
                'package_tracking_numbers' => $packageInfo['tracking_numbers']
            ]
        ]);

        Log::info("Transfer record created successfully", [
            'transfer_id' => $transfer->id,
            'transfer_type' => $transfer->type,
            'transfer_description' => $transfer->description
        ]);

        // Update account balances
        Log::info("Updating account balances", [
            'client_suspense_account_id' => $clientSuspenseAccount->id,
            'business_account_id' => $businessAccount->id,
            'amount' => $packageAdjustmentAmount
        ]);

        // Update account balances using the proper debit/credit methods
        Log::info("Debiting from client suspense account", [
            'account_id' => $clientSuspenseAccount->id,
            'amount' => $packageAdjustmentAmount,
            'balance_before' => $clientSuspenseAccount->balance
        ]);
        $clientSuspenseAccount->debit($packageAdjustmentAmount);  // Money goes out of client suspense
        
        Log::info("Crediting to business account", [
            'account_id' => $businessAccount->id,
            'amount' => $packageAdjustmentAmount,
            'balance_before' => $businessAccount->balance
        ]);
        $businessAccount->credit($packageAdjustmentAmount);       // Money comes into business account
        
        // Record the balance changes in BusinessBalanceHistory for dashboard display
        BusinessBalanceHistory::recordChange(
            $business->id,
            $businessAccount->id,
            $packageAdjustmentAmount,
            'credit',
            $transfer->description,
            'MoneyTransfer',
            $transfer->id,
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_adjustment_amount' => $packageAdjustmentAmount,
                'from_account' => $clientSuspenseAccount->name,
                'to_account' => $businessAccount->name
            ],
            auth()->id()
        );
        
        Log::info("Account balances after debit/credit operations", [
            'client_suspense_balance_after_debit' => $clientSuspenseAccount->fresh()->balance,
            'business_account_balance_after_credit' => $businessAccount->fresh()->balance
        ]);

        Log::info("=== PACKAGE ADJUSTMENT MONEY MOVEMENT COMPLETED ===", [
            'transfer_id' => $transfer->id,
            'amount' => $packageAdjustmentAmount,
            'from_account' => $clientSuspenseAccount->name,
            'to_account' => $businessAccount->name,
            'client_suspense_balance_before' => $clientSuspenseAccount->balance,
            'client_suspense_balance_after' => $clientSuspenseAccount->fresh()->balance,
            'business_account_balance_before' => $businessAccount->balance,
            'business_account_balance_after' => $businessAccount->fresh()->balance,
            'transfer_record_created' => true,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'timestamp' => now()->toISOString()
        ]);

        $transferRecords[] = [
            'item_name' => 'Package Adjustment',
            'amount' => $packageAdjustmentAmount,
            'destination' => $businessAccount->name,
            'transfer_id' => $transfer->id,
            'description' => "Package adjustment money moved to business account"
        ];
    }

    /**
     * Update package tracking records when package adjustments are used
     * This is called when items are actually completed/delivered (Save & Exit)
     */
    private function updatePackageTrackingForAdjustments($invoice, $items)
    {
        try {
            $clientId = $invoice->client_id;
            $businessId = $invoice->business_id;
            $branchId = $invoice->branch_id;
            
            Log::info("=== PACKAGE TRACKING UPDATE STARTED (Save & Exit) ===", [
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
                        'package_name' => $pkg->packageItem->display_name,
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
                        'package_name' => $packageTracking->packageItem->display_name,
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
                                    'package_name' => $packageTracking->packageItem->display_name,
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
            
            Log::info("=== PACKAGE TRACKING UPDATE COMPLETED (Save & Exit) ===", [
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
            throw $e;
        }
    }

    /**
     * Create package sales records and account statements for package items
     * This is called when package items are actually used (Save & Exit)
     */
    private function createPackageSalesAndStatements($invoice, $items)
    {
        try {
            Log::info("=== PACKAGE SALES AND STATEMENTS CREATION STARTED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'business_id' => $invoice->business_id,
                'branch_id' => $invoice->branch_id,
                'package_adjustment' => $invoice->package_adjustment,
                'items_count' => count($items)
            ]);

            // Get client's valid package tracking records
            $validPackages = \App\Models\PackageTracking::where('client_id', $invoice->client_id)
                ->where('business_id', $invoice->business_id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->where('valid_until', '>=', now()->toDateString())
                ->with(['packageItem.packageItems.includedItem', 'client'])
                ->get();

            $packageSalesCreated = [];
            $totalPackageAmount = 0;

            foreach ($items as $item) {
                $itemId = $item['id'] ?? $item['item_id'];
                $quantity = $item['quantity'] ?? 1;
                $remainingQuantity = $quantity;
                
                Log::info("Processing item for package sales creation", [
                    'item_id' => $itemId,
                    'item_name' => $item['name'] ?? 'Unknown',
                    'quantity' => $quantity,
                    'remaining_quantity' => $remainingQuantity
                ]);

                // Get the item price (branch-specific or default)
                $itemModel = \App\Models\Item::find($itemId);
                $price = $itemModel ? $itemModel->default_price : 0;
                
                Log::info("Item price calculation for package sales", [
                    'item_id' => $itemId,
                    'item_name' => $itemModel->name ?? 'Unknown',
                    'default_price' => $price,
                    'branch_id' => $invoice->branch_id
                ]);
                
                // Check for branch-specific price
                $branchPrice = \App\Models\BranchItemPrice::where('branch_id', $invoice->branch_id)
                    ->where('item_id', $itemId)
                    ->first();
                
                if ($branchPrice) {
                    $price = $branchPrice->price;
                    Log::info("Using branch-specific price for package sales", [
                        'item_id' => $itemId,
                        'branch_id' => $invoice->branch_id,
                        'branch_price' => $price,
                        'default_price' => $itemModel->default_price
                    ]);
                } else {
                    Log::info("Using default price for package sales", [
                        'item_id' => $itemId,
                        'default_price' => $price
                    ]);
                }

                // Check if this item is included in any valid packages
                foreach ($validPackages as $packageTracking) {
                    if ($remainingQuantity <= 0) break;

                    Log::info("Checking package for item match in package sales", [
                        'package_tracking_id' => $packageTracking->id,
                        'package_name' => $packageTracking->packageItem->display_name,
                        'item_id' => $itemId,
                        'package_remaining_quantity' => $packageTracking->remaining_quantity,
                        'package_status' => $packageTracking->status
                    ]);

                    // Check if the current item is included in this package
                    $packageItems = $packageTracking->packageItem->packageItems;
                    
                    Log::info("Package items in this package", [
                        'package_tracking_id' => $packageTracking->id,
                        'package_items_count' => $packageItems->count(),
                        'package_items' => $packageItems->map(function($pi) {
                            return [
                                'id' => $pi->id,
                                'included_item_id' => $pi->included_item_id,
                                'max_quantity' => $pi->max_quantity,
                                'fixed_quantity' => $pi->fixed_quantity
                            ];
                        })
                    ]);
                    
                    foreach ($packageItems as $packageItem) {
                        if ($packageItem->included_item_id == $itemId) {
                            Log::info("Item found in package for sales creation", [
                                'package_tracking_id' => $packageTracking->id,
                                'package_item_id' => $packageItem->id,
                                'included_item_id' => $packageItem->included_item_id,
                                'max_quantity' => $packageItem->max_quantity,
                                'fixed_quantity' => $packageItem->fixed_quantity
                            ]);
                            // Check max quantity constraint from package_items table
                            $maxQuantity = $packageItem->max_quantity ?? null;
                            $fixedQuantity = $packageItem->fixed_quantity ?? null;
                            
                            Log::info("Package quantity constraints", [
                                'package_tracking_id' => $packageTracking->id,
                                'max_quantity' => $maxQuantity,
                                'fixed_quantity' => $fixedQuantity,
                                'package_remaining_quantity' => $packageTracking->remaining_quantity
                            ]);
                            
                            // Determine how much quantity can be used from this package
                            $availableFromPackage = $packageTracking->remaining_quantity;
                            
                            if ($maxQuantity !== null) {
                                $availableFromPackage = min($availableFromPackage, $maxQuantity);
                                Log::info("Limited by max_quantity constraint for sales", [
                                    'max_quantity' => $maxQuantity,
                                    'available_from_package' => $availableFromPackage
                                ]);
                            } elseif ($fixedQuantity !== null) {
                                $availableFromPackage = min($availableFromPackage, $fixedQuantity);
                                Log::info("Limited by fixed_quantity constraint for sales", [
                                    'fixed_quantity' => $fixedQuantity,
                                    'available_from_package' => $availableFromPackage
                                ]);
                            }
                            
                            // Calculate how much we can actually use
                            $quantityToUse = min($remainingQuantity, $availableFromPackage);
                            
                            Log::info("Calculated quantity to use for package sales", [
                                'remaining_quantity_needed' => $remainingQuantity,
                                'available_from_package' => $availableFromPackage,
                                'quantity_to_use' => $quantityToUse
                            ]);
                            
                            if ($quantityToUse > 0) {
                                // Calculate amount for this package sale
                                $itemAmount = $price * $quantityToUse;
                                $totalPackageAmount += $itemAmount;

                                Log::info("Calculating package sale amount", [
                                    'item_id' => $itemId,
                                    'item_price' => $price,
                                    'quantity_to_use' => $quantityToUse,
                                    'item_amount' => $itemAmount,
                                    'total_package_amount' => $totalPackageAmount
                                ]);

                                // Create PackageSales record
                                // Use tracking_number from database, or generate if null (for backwards compatibility)
                                $trackingNumber = $packageTracking->tracking_number 
                                    ?? "PKG-{$packageTracking->id}-{$packageTracking->created_at->format('YmdHis')}";
                                
                                $packageSaleData = [
                                    'name' => $packageTracking->client->name ?? 'Unknown Client',
                                    'invoice_number' => $invoice->invoice_number,
                                    'pkn' => $trackingNumber,
                                    'date' => now()->toDateString(),
                                    'qty' => $quantityToUse,
                                    'item_name' => $itemModel->name ?? 'Unknown Item',
                                    'amount' => $itemAmount,
                                    'business_id' => $invoice->business_id,
                                    'branch_id' => $invoice->branch_id,
                                    'client_id' => $invoice->client_id,
                                    'package_tracking_id' => $packageTracking->id,
                                    'item_id' => $itemId,
                                    'status' => 'completed',
                                    'notes' => "Package item sale from invoice {$invoice->invoice_number}"
                                ];

                                Log::info("Creating PackageSales record", [
                                    'package_sale_data' => $packageSaleData
                                ]);

                                $packageSale = \App\Models\PackageSales::create($packageSaleData);

                                Log::info("Package sales record created", [
                                    'package_sale_id' => $packageSale->id,
                                    'package_tracking_id' => $packageTracking->id,
                                    'item_id' => $itemId,
                                    'item_name' => $itemModel->name,
                                    'quantity' => $quantityToUse,
                                    'amount' => $itemAmount,
                                    'pkn' => $packageSale->pkn
                                ]);

                                $packageSalesCreated[] = [
                                    'package_sale_id' => $packageSale->id,
                                    'package_tracking_id' => $packageTracking->id,
                                    'item_id' => $itemId,
                                    'item_name' => $itemModel->name,
                                    'quantity' => $quantityToUse,
                                    'amount' => $itemAmount,
                                    'pkn' => $packageSale->pkn
                                ];

                                $remainingQuantity -= $quantityToUse;
                                
                                // If we've used all available quantity from this package, break
                                if ($remainingQuantity <= 0) break;
                            }
                        }
                    }
                }
            }

            // Create client account statement entry (type: package)
            if (count($packageSalesCreated) > 0) {
                $this->createClientPackageStatementEntry($invoice, $packageSalesCreated);
            }

            // Create business account statement entry (type: package)
            if (count($packageSalesCreated) > 0) {
                $this->createBusinessPackageStatementEntry($invoice, $packageSalesCreated);
            }

            Log::info("=== PACKAGE SALES AND STATEMENTS CREATION COMPLETED ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_sales_created' => count($packageSalesCreated),
                'total_package_amount' => $totalPackageAmount,
                'package_sales_details' => $packageSalesCreated,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating package sales and statements", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create client account statement entry for package usage (type: package)
     */
    private function createClientPackageStatementEntry($invoice, $packageSales)
    {
        try {
            $totalAmount = array_sum(array_column($packageSales, 'amount'));
            
            Log::info("=== CREATING CLIENT PACKAGE STATEMENT ENTRY ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'client_name' => $invoice->client->name ?? 'Unknown',
                'total_amount' => $totalAmount,
                'package_sales_count' => count($packageSales),
                'package_sales_breakdown' => $packageSales,
                'description_format' => 'Item + quantity + ref (PKG tracking number)',
                'timestamp' => now()->toDateTimeString()
            ]);

            // Create BalanceHistory record for client statement (type: package)
            // This represents the client using their package items
            
            // Build description with constituent items and quantities in brackets, with package tracking numbers as ref
            $constituentItems = [];
            $packageTrackingNumbers = [];
            
            foreach ($packageSales as $sale) {
                $packageTracking = \App\Models\PackageTracking::find($sale['package_tracking_id']);
                if ($packageTracking && $packageTracking->tracking_number) {
                    $trackingNumber = $packageTracking->tracking_number; // Use the actual tracking number from database (format: PKG-X-YmdHis)
                    $constituentItems[] = "{$sale['item_name']} ({$sale['quantity']}) (Ref: {$trackingNumber})";
                    $packageTrackingNumbers[] = $trackingNumber;
                }
            }
            
            $constituentItemsDescription = implode(', ', $constituentItems);
            $trackingNumbersRef = implode(', ', array_unique($packageTrackingNumbers));
            
            $balanceHistory = \App\Models\BalanceHistory::recordPackageUsage(
                $invoice->client,
                $totalAmount,
                "{$constituentItemsDescription} from invoice {$invoice->invoice_number}",
                $invoice->invoice_number,
                $constituentItemsDescription,
                'package_usage'
            );

            Log::info("Client package statement entry created successfully", [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'balance_history_id' => $balanceHistory->id,
                'total_amount' => $totalAmount,
                'transaction_type' => 'package',
                'description' => $constituentItemsDescription,
                'description_format' => 'Item + quantity + ref (PKG tracking number)',
                'reference_number' => $invoice->invoice_number,
                'client_name' => $invoice->client->name ?? 'Unknown',
                'package_sales_count' => count($packageSales),
                'package_items_used' => $constituentItemsDescription,
                'package_tracking_numbers' => $trackingNumbersRef,
                'note' => 'Package usage shows item amount for display but does not affect client balance'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create client package statement entry", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'package_sales_data' => $packageSales,
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }

    /**
     * Create business account statement entry for package sales (type: package)
     */
    private function createBusinessPackageStatementEntry($invoice, $packageSales)
    {
        try {
            // For business statement, we should use the sum of item amounts, not the package amount
            // The business should see the actual revenue from individual items sold (113), not the package price (120)
            $totalAmount = array_sum(array_column($packageSales, 'amount'));
            
            Log::info("=== CREATING BUSINESS PACKAGE STATEMENT ENTRY ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'business_id' => $invoice->business_id,
                'business_name' => $invoice->business->name ?? 'Unknown',
                'branch_id' => $invoice->branch_id,
                'business_revenue_amount' => $totalAmount,
                'item_amounts_sum' => array_sum(array_column($packageSales, 'amount')),
                'package_sales_count' => count($packageSales),
                'package_sales_breakdown' => $packageSales,
                'description_format' => 'Package name + ref (PKG tracking number)',
                'note' => 'Business statement uses sum of item amounts (actual revenue), not package price',
                'timestamp' => now()->toDateTimeString(),
                'fixes_applied' => [
                    'removed_plus_sign_from_package_amounts' => true,
                    'simplified_package_descriptions' => true,
                    'updated_description_format' => 'Package name + ref (PKG tracking number)',
                    'removed_verbose_prefixes' => true
                ]
            ]);

            // Create BusinessBalanceHistory record for business statement (type: package)
            // This represents the business receiving revenue from package item sales
            $business = $invoice->business;
            $businessAccount = $this->getOrCreateBusinessAccount($business);
            
            Log::info("Business account details for package statement", [
                'business_id' => $business->id,
                'business_account_id' => $businessAccount->id,
                'business_account_balance' => $businessAccount->balance,
                'business_account_name' => $businessAccount->name
            ]);
            
            // For package sales revenue, we should use 'package' type to avoid double crediting
            // The actual money transfer already happened in the package adjustment money movement
            
            // Build simplified description with package names and tracking numbers
            $packageDescriptions = [];
            $trackingNumbers = [];
            
            foreach ($packageSales as $sale) {
                $packageTracking = \App\Models\PackageTracking::find($sale['package_tracking_id']);
                if ($packageTracking && $packageTracking->tracking_number) {
                    $packageName = $packageTracking->packageItem->display_name ?? 'Unknown Package';
                    $trackingNumber = $packageTracking->tracking_number; // Use the actual tracking number from database (format: PKG-X-YmdHis)
                    $packageDescriptions[] = "{$packageName} (Ref: {$trackingNumber})";
                    $trackingNumbers[] = $trackingNumber;
                }
            }
            
            // Create tracking numbers reference string
            $trackingNumbersRef = implode(', ', $trackingNumbers);
            
            // Use first package for main description, add "and X more" if multiple packages
            if (count($packageDescriptions) == 1) {
                $packageDescription = $packageDescriptions[0];
            } else {
                $packageDescription = $packageDescriptions[0] . " and " . (count($packageDescriptions) - 1) . " more";
            }
            
            $businessBalanceHistory = \App\Models\BusinessBalanceHistory::recordPackageTransaction(
                $business->id,
                $businessAccount->id,
                $totalAmount,
                "{$packageDescription} from invoice {$invoice->invoice_number}",
                'package_sales',
                $invoice->id,
                [
                    'invoice_id' => $invoice->id,
                    'package_sales_count' => count($packageSales),
                    'package_items_sold' => implode(', ', array_column($packageSales, 'item_name')),
                    'package_amount' => $totalAmount,
                    'item_amounts_sum' => array_sum(array_column($packageSales, 'amount')),
                    'package_tracking_numbers' => $trackingNumbersRef,
                    'note' => 'Record only - money already transferred via package adjustment'
                ],
                null
            );

            Log::info("Business package statement entry created successfully", [
                'invoice_id' => $invoice->id,
                'business_id' => $invoice->business_id,
                'business_balance_history_id' => $businessBalanceHistory->id,
                'total_amount' => $totalAmount,
                'transaction_type' => 'package',
                'description_used' => "{$packageDescription} from invoice {$invoice->invoice_number}",
                'business_statement_fixes' => [
                    'no_plus_sign_on_package_amounts' => true,
                    'simplified_descriptions' => true,
                    'timestamp_format_tracking_numbers' => true
                ],
                'description' => "Package sales revenue: {$packageDescription} from invoice {$invoice->invoice_number}",
                'description_format' => 'Package name + ref (PKG tracking number)',
                'reference_type' => 'package_sales',
                'reference_id' => $invoice->id,
                'business_account_balance' => $businessAccount->balance,
                'package_sales_count' => count($packageSales),
                'package_items_sold' => implode(', ', array_column($packageSales, 'item_name')),
                'package_tracking_numbers' => $trackingNumbersRef,
                'note' => 'Package sales revenue recorded as package type (no balance change) - money already transferred via package adjustment'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create business package statement entry", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'business_id' => $invoice->business_id,
                'package_sales_data' => $packageSales,
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }
}
