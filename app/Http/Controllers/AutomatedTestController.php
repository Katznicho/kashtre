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

            // Get selected items and payment phone from request
            $selectedItemIds = $request->input('items', []);
            $paymentPhone = $request->input('payment_phone', '');

            // STEP 1: Register a user (Create Client)
            $output[] = "STEP 1️⃣ : USER REGISTRATION\n";
            $output[] = "----------------------------\n";
            
            $clientPhone = '0777' . rand(100000, 999999);
            $client = Client::create([
                'name' => 'Test User ' . now()->timestamp,
                'phone' => $clientPhone,
                'email' => 'testuser' . rand(10000, 99999) . '@test.com',
                'payment_phone' => $paymentPhone ?: '0777' . rand(100000, 999999),
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'category' => 'general',
            ]);
            
            $output[] = "✅ User Registered\n";
            $output[] = "   Name: {$client->name}\n";
            $output[] = "   Phone: {$client->phone}\n";
            $output[] = "   Payment Phone: {$client->payment_phone}\n";
            $output[] = "   Email: {$client->email}\n\n";

            // STEP 2: User Orders Items
            $output[] = "STEP 2️⃣ : USER ORDERS ITEMS\n";
            $output[] = "----------------------------\n";

            // Get items - use selected items or get defaults
            if (!empty($selectedItemIds)) {
                $items = Item::whereIn('id', $selectedItemIds)->where('business_id', $business->id)->get();
            } else {
                $items = Item::where('business_id', $business->id)->limit(3)->get();
            }
            
            if ($items->count() < 1) {
                $output[] = "⚠️ No items available. Creating test items...\n";
                $group = Group::where('business_id', $business->id)->first() ?? 
                    Group::create(['name' => 'Test Items Group', 'business_id' => $business->id]);

                for ($i = 1; $i <= 3; $i++) {
                    Item::create([
                        'name' => "Test Item {$i}",
                        'type' => 'good',
                        'business_id' => $business->id,
                        'group_id' => $group->id,
                        'default_price' => 10000,
                    ]);
                }
                $items = Item::where('business_id', $business->id)->limit(3)->get();
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

                $output[] = "   • {$item->name} - Qty: {$quantity}, Price: {$unitPrice}\n";
            }
            
            $output[] = "\n";

            // STEP 3: Create Invoice (Order)
            $output[] = "STEP 3️⃣ : PROCESS PAYMENT\n";
            $output[] = "----------------------------\n";

            $invoice = Invoice::create([
                'invoice_number' => 'ORD' . now()->timestamp,
                'client_id' => $client->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'created_by' => Auth::id(),
                'client_name' => $client->name,
                'client_phone' => $client->phone,
                'payment_phone' => $client->payment_phone,
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

            // STEP 4: Payment Processing
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'amount' => $totalAmount,
                'payment_method' => 'cash',
                'transaction_type' => 'payment',
                'status' => 'completed',
                'reference_number' => 'PAY' . now()->timestamp,
            ]);

            // Update invoice to paid
            $invoice->update([
                'amount_paid' => $totalAmount,
                'balance_due' => 0,
                'payment_status' => 'paid',
                'confirmed_at' => now(),
            ]);

            $output[] = "✅ Payment Completed\n";
            $output[] = "   Reference: {$transaction->reference_number}\n";
            $output[] = "   Amount: {$totalAmount}\n";
            $output[] = "   Status: Paid\n\n";

            // STEP 5: Queue Items
            $output[] = "STEP 4️⃣ : ITEMS QUEUED FOR DELIVERY\n";
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
                    'status' => 'waiting',
                    'created_by' => Auth::id(),
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


