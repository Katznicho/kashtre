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
     * Step 1: Payment received - Money goes to client account
     */
    public function processPaymentReceived(Client $client, $amount, $reference, $paymentMethod, $metadata = [])
    {
        try {
            DB::beginTransaction();

            $clientAccount = $this->getOrCreateClientAccount($client);
            
            // Create transfer record
            $transfer = MoneyTransfer::create([
                'business_id' => $client->business_id,
                'from_account_id' => null, // External payment
                'to_account_id' => $clientAccount->id,
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

            // Credit client account
            $clientAccount->debit($amount);

            DB::commit();
            
            Log::info("Payment received: {$amount} UGX for client {$client->name}", [
                'client_id' => $client->id,
                'reference' => $reference,
                'transfer_id' => $transfer->id
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
     * Step 2: Order confirmed - Money moves to suspense accounts
     */
    public function processOrderConfirmed(Invoice $invoice, $items)
    {
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
     * Process service charge
     */
    public function processServiceCharge(Invoice $invoice, $serviceChargeAmount)
    {
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

        // Update account balances
        $fromAccount->credit($amount);
        $toAccount->debit($amount);

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
            
            // Get the item amount (considering package adjustments)
            $itemAmount = $this->calculateItemAmount($invoice, $item);
            
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
            
            // Record business balance history
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
                
                // Record Kashtre balance history
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
                    
                    // Record contractor balance history
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
                    
                    // Record business balance history for contractor share
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
                    
                    // Update business account balance
                    $business->increment('account_balance', $businessShare);
                }
            }
            
            // Update business account balance (business share amount)
            $business->increment('account_balance', $businessShare);
            
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
    private function getOrCreateBusinessAccount(Business $business)
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
    private function getOrCreateKashtreAccount()
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
}
