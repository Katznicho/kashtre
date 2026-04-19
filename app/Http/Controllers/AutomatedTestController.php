<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\ServiceQueue;
use App\Models\ServicePoint;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutomatedTestController extends Controller
{
    /**
     * Display the automated test page.
     */
    public function index()
    {
        return view('automated-tests.index');
    }

    /**
     * Get available items for selection.
     */
    public function getItems()
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->business_id) {
                return response()->json([], 400);
            }

            $items = Item::where('business_id', $user->business_id)
                ->whereIn('type', ['service', 'good', 'package', 'bulk'])
                ->select('id', 'name', 'type', 'default_price')
                ->orderBy('name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name . ' (' . ucfirst($item->type) . ')',
                        'price' => $item->default_price ?? 0,
                        'type' => $item->type
                    ];
                });

            return response()->json($items);
        } catch (\Exception $e) {
            Log::error('Error in getItems: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Run one complete user journey test.
     */
    public function run(Request $request)
    {
        $output = [];
        $output[] = "🧪 Starting Complete User Journey Test...\n";
        $output[] = "====================================================\n\n";

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $business = Business::find($user->business_id);
            $branch = Branch::where('business_id', $user->business_id)->first();

            if (!$business || !$branch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing business or branch',
                    'output' => "❌ Error: Business or branch not found"
                ]);
            }

            $output[] = "📍 Business: {$business->name}\n";
            $output[] = "📍 Branch: {$branch->name}\n";
            $output[] = "====================================================\n\n";

            // Get payment phone, item count, item types, and max amount from request
            $paymentPhone = $request->input('payment_phone', '');
            $itemCount = intval($request->input('item_count', 3));
            $itemCount = max(1, min($itemCount, 10)); // Ensure between 1 and 10
            $maxAmount = intval($request->input('max_amount', 100000));
            $maxAmount = max(1000, $maxAmount); // Minimum 1000 UGX
            $itemTypes = $request->input('item_types', ['service', 'good', 'package', 'bulk']);
            $itemTypes = is_array($itemTypes) ? $itemTypes : ['service', 'good', 'package', 'bulk'];

            // STEP 1: Register a user (Create Client)
            $output[] = "STEP 1️⃣ : USER REGISTRATION\n";
            $output[] = "----------------------------\n";
            
            $clientId = strtoupper(substr('TST', 0, 3)) . '-' . rand(10000, 99999);
            $firstName = 'Test';
            $surname = 'User ' . now()->timestamp;
            $clientPhone = '0777' . rand(100000, 999999);
            $paymentPhoneNumber = $paymentPhone ?: '0776' . rand(100000, 999999);
            
            $client = Client::create([
                'client_type' => 'individual',
                'client_id' => $clientId,
                'visit_id' => 'VIS-' . strtoupper(Str::random(8)),
                'visit_expires_at' => now()->addDays(7),
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'name' => $firstName . ' ' . $surname,
                'first_name' => $firstName,
                'surname' => $surname,
                'phone_number' => $clientPhone,
                'payment_phone_number' => $paymentPhoneNumber,
                'email' => 'testuser' . rand(10000, 99999) . '@test.com',
                'services_category' => 'outpatient',
                'payment_methods' => [],
                'status' => 'active',
                'balance' => 0,
            ]);
            
            $output[] = "✅ User Registered\n";
            $output[] = "   Client ID: {$client->client_id}\n";
            $output[] = "   Name: {$client->name}\n";
            $output[] = "   Phone: {$client->phone_number}\n";
            $output[] = "   Payment Phone: {$client->payment_phone_number}\n";
            $output[] = "   Email: {$client->email}\n\n";

            // STEP 2: User Orders Items (with budget constraint)
            $output[] = "STEP 2️⃣ : USER ORDERS ITEMS\n";
            $output[] = "----------------------------\n";
            $output[] = "📍 Budget: " . number_format($maxAmount) . " UGX\n\n";

            // Get random items from business inventory with selected types
            $availableItems = Item::where('business_id', $business->id)
                ->whereIn('type', $itemTypes)
                ->get()
                ->shuffle();

            if ($availableItems->count() < 1) {
                $output[] = "⚠️ No items available in selected types. Creating test items...\n";
                $group = Group::where('business_id', $business->id)->first() ?? 
                    Group::create(['name' => 'Test Items Group', 'business_id' => $business->id]);

                for ($i = 1; $i <= $itemCount; $i++) {
                    Item::create([
                        'name' => "Test Item {$i}",
                        'type' => $itemTypes[($i - 1) % count($itemTypes)],
                        'business_id' => $business->id,
                        'group_id' => $group->id,
                        'default_price' => 10000,
                    ]);
                }
                $availableItems = Item::where('business_id', $business->id)
                    ->whereIn('type', $itemTypes)
                    ->get()
                    ->shuffle();
            }

            // Select items based on budget constraint
            $items = collect();
            $runningTotal = 0;
            $itemsAdded = 0;

            foreach ($availableItems as $item) {
                // Stop if we've reached the desired item count
                if ($itemsAdded >= $itemCount) {
                    break;
                }

                $unitPrice = $item->default_price ?? 10000;
                $itemCost = $unitPrice;

                // Check if adding this item would exceed budget
                if ($runningTotal + $itemCost <= $maxAmount) {
                    $items->push($item);
                    $runningTotal += $itemCost;
                    $itemsAdded++;
                }
            }

            // If no items fit in budget, add at least one cheapest item
            if ($items->count() === 0 && $availableItems->count() > 0) {
                $cheapest = $availableItems->sortBy('default_price')->first();
                $items->push($cheapest);
                $runningTotal = $cheapest->default_price ?? 10000;
            }

            $output[] = "✅ Items Selected for Order\n";
            
            $invoiceItems = [];
            $totalAmount = 0;
            
            foreach ($items as $index => $item) {
                $quantity = 1;
                $unitPrice = $item->default_price ?? 10000;
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $invoiceItems[] = [
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal,
                ];

                $itemType = ucfirst($item->type ?? 'unknown');
                $output[] = "   • {$item->name} ({$itemType}) - Qty: {$quantity} × " . number_format($unitPrice) . " = " . number_format($itemTotal) . " UGX\n";
            }
            
            $output[] = "\n";
            $output[] = "📊 ORDER SUMMARY\n";
            $output[] = "   Budget Limit: " . number_format($maxAmount) . " UGX\n";
            $output[] = "   Total Amount: " . number_format($totalAmount) . " UGX\n";
            $output[] = "   Items Count: " . $items->count() . "\n";
            $output[] = "   Remaining Budget: " . number_format($maxAmount - $totalAmount) . " UGX\n";
            $output[] = "\n";

            // STEP 3: Create Invoice (Order)
            $output[] = "STEP 3️⃣ : ORDER CONFIRMATION\n";
            $output[] = "----------------------------\n";

            $invoice = Invoice::create([
                'invoice_number' => 'ORD' . now()->timestamp,
                'client_id' => $client->id,
                'visit_id' => $client->visit_id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'created_by' => Auth::id(),
                'client_name' => $client->name,
                'client_phone' => $client->phone_number,
                'payment_phone' => $client->payment_phone_number,
                'items' => $invoiceItems,
                'subtotal' => $totalAmount,
                'service_charge' => 0,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance_due' => $totalAmount,
                'payment_status' => 'unpaid',
                'payment_methods' => [],
                'status' => 'pending',
                'currency' => 'UGX',
            ]);

            $output[] = "✅ Order Created\n";
            $output[] = "   Invoice #: {$invoice->invoice_number}\n";
            $output[] = "   Total Amount: " . number_format($totalAmount) . " UGX\n";
            $output[] = "   Items: " . count($invoiceItems) . "\n\n";

            // STEP 4: Process Payment (Call YoAPI)
            $output[] = "STEP 4️⃣ : PAYMENT PROCESSING\n";
            $output[] = "----------------------------\n";
            
            // Prepare items description for payment gateway
            $itemsDescription = [];
            foreach ($invoiceItems as $item) {
                $itemsDescription[] = $item['name'];
            }
            $paymentNarrative = "Order " . $invoice->invoice_number . ": " . implode(", ", $itemsDescription);
            
            $output[] = "💳 Initiating Mobile Money Payment...\n";
            $output[] = "   Phone: {$client->payment_phone_number}\n";
            $output[] = "   Amount: " . number_format($totalAmount) . " UGX\n";
            
            // Format phone number for YoAPI (must be international format 256XXXXXXXXX)
            $formattedPhone = $client->payment_phone_number;
            $formattedPhone = preg_replace('/[^0-9+]/', '', $formattedPhone);
            
            if (str_starts_with($formattedPhone, '+256')) {
                $formattedPhone = substr($formattedPhone, 1);
            } elseif (str_starts_with($formattedPhone, '0')) {
                $formattedPhone = '256' . substr($formattedPhone, 1);
            }
            
            $paymentReference = null;
            $paymentStatus = 'pending';
            
            try {
                $yoPayments = new \App\Payments\YoAPI(
                    config('payments.yo_username'),
                    config('payments.yo_password')
                );
                
                $yoPayments->set_external_reference('OR' . $invoice->invoice_number);
                
                $paymentResult = $yoPayments->ac_deposit_funds(
                    $formattedPhone,
                    intval($totalAmount),
                    $paymentNarrative
                );
                
                Log::info('YoAPI Payment Result', ['result' => $paymentResult]);
                
                if (isset($paymentResult['Status']) && $paymentResult['Status'] === 'OK') {
                    $output[] = "✅ Payment Request Initiated\n";
                    $paymentReference = $paymentResult['TransactionReference'] ?? 'YO' . now()->timestamp;
                    $paymentStatus = 'pending';
                    $output[] = "   Status: Awaiting Customer Confirmation\n";
                } else {
                    $output[] = "⚠️ Payment Failed: " . ($paymentResult['StatusMessage'] ?? 'Unknown error') . "\n";
                    $paymentReference = 'FAILED-' . now()->timestamp;
                    $paymentStatus = 'failed';
                }
            } catch (\Exception $e) {
                $output[] = "⚠️ Payment Error: " . $e->getMessage() . "\n";
                Log::error('Payment Error', ['error' => $e->getMessage()]);
                $paymentReference = 'ERROR-' . now()->timestamp;
                $paymentStatus = 'failed';
            }
            
            $output[] = "\n";
            
            // Create transaction record
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'amount' => $totalAmount,
                'reference' => $invoice->invoice_number,
                'external_reference' => $paymentReference,
                'service' => 'healthcare',
                'status' => $paymentStatus,
                'type' => 'credit',
                'origin' => 'web',
                'method' => 'mobile_money',
                'provider' => 'yo',
                'phone_number' => $formattedPhone,
                'description' => $paymentNarrative,
            ]);

            // Update invoice payment status
            $invoice->update([
                'payment_status' => $paymentStatus === 'pending' ? 'pending' : 'failed',
            ]);

            // STEP 5: Awaiting Automatic Confirmation
            $output[] = "STEP 5️⃣ : AWAITING AUTOMATIC CONFIRMATION\n";
            $output[] = "-------------------------------------\n";
            
            if ($paymentStatus === 'pending') {
                $output[] = "⏳ Payment Pending Confirmation\n";
                $output[] = "   Invoice: {$invoice->invoice_number}\n";
                $output[] = "   Amount: " . number_format($totalAmount) . " UGX\n";
                $output[] = "   Phone: {$client->payment_phone_number}\n\n";
                
                $output[] = "🤖 CRON JOB AUTOMATION:\n";
                $output[] = "   • Runs every 5 minutes (payments:check-status)\n";
                $output[] = "   • Checks YoAPI for payment confirmation\n";
                $output[] = "   • When payment confirmed:\n";
                $output[] = "     ✅ Transaction marked as 'completed'\n";
                $output[] = "     ✅ Invoice marked as 'paid'\n";
                $output[] = "     ✅ Items automatically queued to service point\n\n";
                
                $output[] = "📱 Customer will receive prompt on: {$client->payment_phone_number}\n";
            } else {
                $output[] = "❌ Payment Failed - Manual Review Needed\n";
                $output[] = "   Invoice: {$invoice->invoice_number}\n";
                $output[] = "   Status: " . ucfirst($paymentStatus) . "\n";
            }

            // SUMMARY
            $output[] = "====================================================\n";
            if ($paymentStatus === 'pending') {
                $output[] = "✅ AUTOMATED TEST COMPLETED!\n";
                $output[] = "====================================================\n\n";
                $output[] = "🎯 ORDER SUBMITTED FOR PAYMENT:\n";
                $output[] = "• User: {$client->name}\n";
                $output[] = "• Client ID: {$client->client_id}\n";
                $output[] = "• Invoice: {$invoice->invoice_number}\n";
                $output[] = "• Amount: " . number_format($totalAmount) . " UGX\n";
                $output[] = "• Items: " . count($invoiceItems) . "\n";
                $output[] = "• Payment Phone: {$client->payment_phone_number}\n\n";
                
                $output[] = "⏳ NEXT: AUTOMATIC PROCESSING\n";
                $output[] = "   The system will automatically (every 5 minutes):\n";
                $output[] = "   1. Confirm payment status with YoAPI\n";
                $output[] = "   2. Mark transaction as 'completed'\n";
                $output[] = "   3. Mark invoice as 'paid'\n";
                $output[] = "   4. Queue items to service point\n\n";
                
                $output[] = "📊 MONITORING:\n";
                $output[] = "   Invoice: {$invoice->invoice_number}\n";
                $output[] = "   Transaction Ref: {$transaction->id}\n";
                $output[] = "   Check payment status in database\n";
            } else {
                $output[] = "❌ TEST FAILED - PAYMENT ERROR\n";
                $output[] = "====================================================\n\n";
                $output[] = "⚠️ Order could not be submitted:\n";
                $output[] = "• User: {$client->name}\n";
                $output[] = "• Invoice: {$invoice->invoice_number}\n";
                $output[] = "• Error: Payment initiation failed\n";
                $output[] = "• Amount: " . number_format($totalAmount) . " UGX\n\n";
                
                $output[] = "❌ ACTION REQUIRED:\n";
                $output[] = "   Please check YoAPI configuration and try again\n";
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'output' => implode('', $output)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $output[] = "\n❌ TEST FAILED\n";
            $output[] = "Error: " . $e->getMessage() . "\n";

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'output' => implode('', $output)
            ], 500);
        }
    }
}


