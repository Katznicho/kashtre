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
}
