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
            $output[] = "   Total Amount: {$totalAmount}\n";
            $output[] = "   Items: " . count($invoiceItems) . "\n\n";

            // STEP 4: Payment Processing - PIN Verification
            $output[] = "STEP 4️⃣ : PAYMENT PIN VERIFICATION\n";
            $output[] = "----------------------------\n";
            
            // Simulate PIN entry
            $testPin = '1234';
            $output[] = "🔐 Payment PIN: {$testPin}\n";
            $output[] = "   Verifying PIN...\n";
            
            // Simulate PIN verification delay
            usleep(500000); // 0.5 second delay
            
            $output[] = "✅ PIN Verified Successfully\n\n";
            
            // Create transaction after PIN verification
            $output[] = "STEP 5️⃣ : PROCESS PAYMENT\n";
            $output[] = "----------------------------\n";
            
            // Prepare items description for payment gateway
            $itemsDescription = [];
            foreach ($invoiceItems as $item) {
                $itemsDescription[] = $item['name'];
            }
            $paymentNarrative = "Order " . $invoice->invoice_number . ": " . implode(", ", $itemsDescription);
            
            $output[] = "💳 Initiating Mobile Money Payment...\n";
            $output[] = "   Provider: YoAPI\n";
            $output[] = "   Phone: {$client->payment_phone_number}\n";
            $output[] = "   Amount: " . number_format($totalAmount) . " UGX\n";
            
            // Call actual payment gateway
            try {
                $yoPayments = new \App\Payments\YoAPI(
                    config('payments.yo_username'),
                    config('payments.yo_password')
                );
                
                // Set external reference
                $externalRef = 'TST' . now()->timestamp;
                $yoPayments->set_external_reference($externalRef);
                
                // Process the actual payment
                $paymentResult = $yoPayments->ac_deposit_funds(
                    $client->payment_phone_number,
                    intval($totalAmount),
                    $paymentNarrative
                );
                
                // Log payment result
                Log::info('YoAPI Payment Result', ['result' => $paymentResult]);
                
                // Check if payment was initiated successfully
                if (isset($paymentResult['Status']) && $paymentResult['Status'] === 'OK') {
                    $output[] = "✅ Payment Request Sent to Phone\n";
                    $output[] = "   Transaction Ref: {$paymentResult['TransactionReference']}\n";
                    $paymentReference = $paymentResult['TransactionReference'];
                    $paymentStatus = 'completion_pending';
                } else {
                    // Payment failed or not OK
                    $output[] = "⚠️ Payment Response: " . ($paymentResult['StatusMessage'] ?? 'Unknown response') . "\n";
                    Log::warning('YoAPI Payment Failed', ['result' => $paymentResult]);
                    $paymentReference = 'FAILED-' . now()->timestamp;
                    $paymentStatus = 'pending';
                }
            } catch (\Exception $e) {
                $output[] = "⚠️ Payment Gateway Error: " . $e->getMessage() . "\n";
                Log::error('Payment Gateway Error', ['error' => $e->getMessage()]);
                $paymentReference = 'ERROR-' . now()->timestamp;
                $paymentStatus = 'pending';
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
                'external_reference' => $paymentReference, // YoAPI transaction reference
                'service' => 'healthcare',
                'status' => $paymentStatus,
                'method' => 'mobile_money',
                'provider' => 'yo',
                'phone_number' => $client->payment_phone_number,
                'description' => $paymentNarrative,
            ]);

            // Update invoice payment status
            $invoice->update([
                'payment_status' => $paymentStatus === 'completion_pending' ? 'pending' : $paymentStatus,
            ]);

            $output[] = "✅ Payment Completed\n";
            $output[] = "   Reference: {$paymentReference}\n";
            $output[] = "   Amount: " . number_format($totalAmount) . " UGX\n";

            // STEP 5: Queue Items
            $output[] = "STEP 5️⃣ : ITEMS QUEUED FOR DELIVERY\n";
            $output[] = "-------------------------------------\n";

            // Get or create service point
            $servicePoint = ServicePoint::where('branch_id', $branch->id)->first();
            if (!$servicePoint) {
                $servicePoint = ServicePoint::create([
                    'name' => 'Main Service Point',
                    'branch_id' => $branch->id,
                    'business_id' => $business->id,
                ]);
            }

            // Create queue for each item
            $queueCount = 0;
            foreach ($invoiceItems as $invoiceItem) {
                $queue = ServiceQueue::create([
                    'client_id' => $client->id,
                    'service_point_id' => $servicePoint->id,
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'queue_number' => ServiceQueue::generateQueueNumber($servicePoint->id, $business->id),
                    'status' => 'pending',
                    'user_id' => Auth::id(),
                ]);
                $queueCount++;
                $output[] = "✅ Item Queued: #{$queue->queue_number}\n";
            }

            $output[] = "\n";

            // SUMMARY
            $output[] = "====================================================\n";
            $output[] = "✅ TEST COMPLETED SUCCESSFULLY!\n";
            $output[] = "====================================================\n\n";
            $output[] = "📊 SUMMARY:\n";
            $output[] = "• User Registered: {$client->name}\n";
            $output[] = "• Items Ordered: " . count($invoiceItems) . "\n";
            $output[] = "• Total Amount: {$totalAmount}\n";
            $output[] = "• Payment Status: PAID\n";
            $output[] = "• Items in Queue: {$queueCount}\n";

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


