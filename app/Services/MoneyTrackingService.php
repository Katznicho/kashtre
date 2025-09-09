<?php

namespace App\Services;

use App\Models\MoneyAccount;
use App\Models\MoneyTransfer;
use App\Models\PackageTracking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Business;
use App\Models\ContractorProfile;
use App\Models\BalanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\BusinessBalanceHistory;
use App\Models\ContractorBalanceHistory;

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
                'description' => "Payment received via {$paymentMethod}",
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
                        "Package item usage: {$item->name}"
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
                    "Payment received via {$primaryMethod}",
                    $invoice->invoice_number,
                    "Payment received for invoice",
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
                $debitRecord = BalanceHistory::recordDebit(
                    $client,
                    $totalAmount,
                    "Payment for: {$item->name} (x{$quantity})",
                    $invoice->invoice_number,
                    "Payment completed - Item purchased: {$item->name}"
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
                    'quantity' => $quantity,
                    'amount' => $totalAmount,
                    'balance_history_id' => $debitRecord->id ?? null
                ]);
            }

            // Create debit record for service charge if applicable
            if ($invoice->service_charge > 0) {
                $serviceChargeRecord = BalanceHistory::recordDebit(
                    $client,
                    $invoice->service_charge,
                    "Service Charge Payment",
                    $invoice->invoice_number,
                    "Service charge payment for invoice {$invoice->invoice_number}"
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
            
            // Get the item amount from the service delivery queue record
            $itemAmount = $serviceDeliveryQueue->price * $serviceDeliveryQueue->quantity;
            
            // Get service charge for this invoice
            $serviceCharge = $invoice->service_charge ?? 0;
            
            // Get business account
            $businessAccount = $this->getOrCreateBusinessAccount($business);
            
            // Get Kashtre account (business_id = 1)
            $kashtreAccount = $this->getOrCreateKashtreAccount();
            
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
            
            Log::info("Service delivery money transfer processed", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'item_amount' => $itemAmount,
                'service_charge' => $serviceCharge,
                'business_id' => $business->id
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
     */
    private function calculateItemAmount($invoice, $item)
    {
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
            return 0;
        }
        
        $originalAmount = ($invoiceItem['quantity'] ?? 0) * ($invoiceItem['price'] ?? 0);
        
        // Apply package adjustment if any
        if ($invoice->package_adjustment > 0) {
            // Calculate package adjustment for this specific item
            $packageAdjustment = $this->calculateItemPackageAdjustment($invoice, $item);
            $originalAmount -= $packageAdjustment;
        }
        
        return max(0, $originalAmount);
    }
    
    /**
     * Calculate package adjustment for a specific item
     */
    private function calculateItemPackageAdjustment($invoice, $item)
    {
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
            return 0;
        }
        
        $itemTotal = ($invoiceItem['quantity'] ?? 0) * ($invoiceItem['price'] ?? 0);
        
        // Calculate total invoice amount from items array
        $totalInvoiceAmount = 0;
        if ($invoice->items && is_array($invoice->items)) {
            foreach ($invoice->items as $invItem) {
                $totalInvoiceAmount += ($invItem['quantity'] ?? 0) * ($invItem['price'] ?? 0);
            }
        }
        
        if ($totalInvoiceAmount > 0) {
            return ($itemTotal / $totalInvoiceAmount) * $invoice->package_adjustment;
        }
        
        return 0;
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
                'business_id' => $invoice->business_id,
                'items_count' => count($items),
                'service_charge' => $invoice->service_charge ?? 0
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
                    $this->processRegularItem($item, $totalAmount, $clientSuspenseAccount, $business, $invoice, $transferRecords);
                }

            }

            // Handle service charge if applicable (only once per invoice and when item is completed or partially done)
            if ($invoice->service_charge > 0 && in_array($itemStatus, ['completed', 'partially_done'])) {
                // Check if service charge has already been processed for this invoice (any transfer type)
                $existingServiceChargeTransfer = \App\Models\MoneyTransfer::where('invoice_id', $invoice->id)
                    ->where('description', 'like', '%Service charge for invoice%')
                    ->first();
                
                $shouldProcessServiceCharge = false;
                
                if ($existingServiceChargeTransfer) {
                    // Service charge has already been processed for this invoice - don't process again
                    Log::info("Service charge already processed for this invoice - skipping", [
                        'invoice_id' => $invoice->id,
                        'existing_transfer_id' => $existingServiceChargeTransfer->id,
                        'service_charge_amount' => $invoice->service_charge,
                        'reason' => 'Service charge transfer record already exists for this invoice'
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
            } else {
                if ($invoice->service_charge > 0 && !in_array($itemStatus, ['completed', 'partially_done'])) {
                    Log::info("Service charge not processed - item not completed or partially done", [
                        'invoice_id' => $invoice->id,
                        'service_charge' => $invoice->service_charge,
                        'item_status' => $itemStatus,
                        'reason' => 'Service charge only processed for completed or partially done items'
                    ]);
                } else {
                    Log::info("No service charge to process", [
                        'invoice_id' => $invoice->id,
                        'service_charge' => $invoice->service_charge ?? 0
                    ]);
                }
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
                "Payment received for bulk: {$bulkItem->name}",
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
                "Payment received for bulk: {$bulkItem->name}",
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
    private function processRegularItem($item, $totalAmount, $clientSuspenseAccount, $business, $invoice, &$transferRecords)
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
            $transferDescription = "Contractor payment for: {$item->name}";
            
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
            $transferDescription = "Business payment for: {$item->name}";
            
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
                "Payment received for: {$item->name}",
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
                "Payment received for: {$item->name}",
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
}
