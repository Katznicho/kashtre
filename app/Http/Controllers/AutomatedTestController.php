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
use App\Models\ServiceCharge;
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
        $output[] = "Starting Complete User Journey Test...\n";
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
                    'output' => "ERROR: Business or branch not found"
                ]);
            }

            // Get or create service point for this branch
            $servicePoint = ServicePoint::where('branch_id', $branch->id)->first();
            if (!$servicePoint) {
                $servicePoint = ServicePoint::create([
                    'name' => 'Main Service Point',
                    'branch_id' => $branch->id,
                    'business_id' => $business->id,
                ]);
            }

            $output[] = "=== COMPLETE CUSTOMER JOURNEY TEST ===\n\n";
            $output[] = "Facility: {$business->name}\n";
            $output[] = "Location: {$branch->name}\n";
            $output[] = "Service Point: {$servicePoint->name}\n";
            $output[] = "====================================================\n\n";

            // Get payment phone, item count, item types, and max amount from request
            $paymentPhone = $request->input('payment_phone', '');
            $itemCount = intval($request->input('item_count', 50)); // Allow up to 50 items to fill budget
            $itemCount = max(1, min($itemCount, 100)); // Ensure between 1 and 100
            $maxAmount = intval($request->input('max_amount', 100000));
            $maxAmount = max(500, $maxAmount); // Minimum 500 UGX budget
            $itemTypes = $request->input('item_types', ['service', 'good', 'package', 'bulk']);
            $itemTypes = is_array($itemTypes) ? $itemTypes : ['service', 'good', 'package', 'bulk'];

            // STEP 1: Register a user (Create Client)
            $output[] = "STEP 1: USER REGISTRATION\n";
            $output[] = "----------------------------\n";
            
            $firstName = 'Test';
            $surname = 'User';
            $clientPhone = '0777' . rand(100000, 999999);
            $paymentPhoneNumber = $paymentPhone ?: '0776' . rand(100000, 999999);
            
            // Generate client_id and visit_id using the same methods as real registration
            $clientId = Client::generateClientId($business, $surname, $firstName, null);
            $visitId = Client::generateVisitId($business, $branch, false, false);
            
            $client = Client::create([
                'client_type' => 'individual',
                'client_id' => $clientId,
                'visit_id' => $visitId,
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
            
            $output[] = "[OK] Customer registered successfully\n";
            $output[] = "  Name: {$client->name}\n";
            $output[] = "  Contact: {$client->phone_number}\n";
            $output[] = "  Payment method: {$client->payment_phone_number}\n\n";

            // STEP 2: User Orders Items (with budget constraint)
            $output[] = "STEP 2: CUSTOMER PLACES ORDER\n";
            $output[] = "----------------------------\n";
            $output[] = "Budget available: " . number_format($maxAmount) . " UGX\n";
            $output[] = "Items will be collected from: {$servicePoint->name}\n\n";

            // Get random items from business inventory with selected types
            $availableItems = Item::where('business_id', $business->id)
                ->whereIn('type', $itemTypes)
                ->get()
                ->shuffle();

            if ($availableItems->count() < 1) {
                $output[] = "[WARNING] No items available in selected types. Creating test items...\n";
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

            // Select items based on budget constraint - ensure minimum 500 UGX
            $items = collect();
            $runningTotal = 0;
            $itemsAdded = 0;
            $minimumAmount = 500; // Ensure we always order at least 500 UGX worth

            foreach ($availableItems as $item) {
                $unitPrice = $item->default_price ?? 10000;
                $itemCost = $unitPrice;

                // Keep adding items while:
                // 1. We haven't reached the minimum amount (500 UGX) OR we're still below budget
                // 2. AND we haven't exceeded the max item count
                if ($runningTotal + $itemCost <= $maxAmount && $itemsAdded < $itemCount) {
                    $items->push($item);
                    $runningTotal += $itemCost;
                    $itemsAdded++;
                    
                    // After reaching minimum, still add more items to fill budget better
                    if ($runningTotal >= $minimumAmount && $items->count() >= 5) {
                        break; // Stop after reaching minimum and having reasonable variety
                    }
                }
            }

            // If total is still below minimum, keep adding items until we reach 500
            if ($runningTotal < $minimumAmount && $availableItems->count() > 0) {
                foreach ($availableItems as $item) {
                    if ($items->contains('id', $item->id)) {
                        continue; // Skip already selected items
                    }

                    $unitPrice = $item->default_price ?? 10000;
                    $itemCost = $unitPrice;

                    if ($runningTotal + $itemCost <= $maxAmount) {
                        $items->push($item);
                        $runningTotal += $itemCost;
                    }

                    if ($runningTotal >= $minimumAmount) {
                        break;
                    }
                }
            }

            // If still no items or below minimum, add at least one of the most expensive items
            if ($items->count() === 0 && $availableItems->count() > 0) {
                $mostExpensive = $availableItems->sortByDesc('default_price')->first();
                $items->push($mostExpensive);
                $runningTotal = $mostExpensive->default_price ?? 10000;
            }

            $output[] = "[OK] Items selected\n\n";
            
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
                $output[] = "   • {$item->name} ({$itemType}) - {$quantity} × " . number_format($unitPrice) . " UGX = " . number_format($itemTotal) . " UGX\n";
            }
            
            // Calculate service charge based on Kashtre's configuration
            $serviceChargeAmount = 0;
            $serviceChargeConfig = ServiceCharge::where('business_id', $business->id)
                ->where('entity_type', 'business')
                ->where('is_active', true)
                ->where('lower_bound', '<=', $totalAmount)
                ->where('upper_bound', '>=', $totalAmount)
                ->orderBy('lower_bound', 'desc')
                ->first();
            
            if ($serviceChargeConfig) {
                if ($serviceChargeConfig->type === 'fixed') {
                    $serviceChargeAmount = $serviceChargeConfig->amount;
                } elseif ($serviceChargeConfig->type === 'percentage') {
                    $serviceChargeAmount = ($totalAmount * $serviceChargeConfig->amount) / 100;
                }
            }
            
            $finalAmount = $totalAmount + $serviceChargeAmount;
            
            $output[] = "\n";
            $output[] = "COST BREAKDOWN:\n";
            $output[] = "  Items total: " . number_format($totalAmount) . " UGX\n";
            if ($serviceChargeAmount > 0) {
                $output[] = "  Service charge: " . number_format($serviceChargeAmount) . " UGX\n";
                $output[] = "  Total amount: " . number_format($finalAmount) . " UGX\n";
            } else {
                $output[] = "  Service charge: None\n";
                $output[] = "  Total amount: " . number_format($finalAmount) . " UGX\n";
            }
            $output[] = "  Number of items: " . $items->count() . "\n";
            $output[] = "  Budget used: " . number_format($finalAmount) . " UGX of " . number_format($maxAmount) . " UGX\n";
            $output[] = "\n";

            // STEP 3: Create Invoice (Order)
            $output[] = "STEP 3: REVIEW & CONFIRM ORDER\n";
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
                'service_charge' => $serviceChargeAmount,
                'total_amount' => $finalAmount,
                'amount_paid' => 0,
                'balance_due' => $finalAmount,
                'payment_status' => 'unpaid',
                'payment_methods' => [],
                'status' => 'pending',
                'currency' => 'UGX',
            ]);

            $output[] = "[OK] Order confirmed\n";
            $output[] = "  Order #: {$invoice->invoice_number}\n";
            $output[] = "  Amount to pay: " . number_format($finalAmount) . " UGX\n";
            $output[] = "  Items in order: " . count($invoiceItems) . "\n";
            
            // Calculate projected queue numbers for items
            $currentQueueNumber = ServiceQueue::where('service_point_id', $servicePoint->id)->max('queue_number') ?? 0;
            $projectedQueueNumbers = [];
            for ($i = 1; $i <= count($invoiceItems); $i++) {
                $projectedQueueNumbers[] = $currentQueueNumber + $i;
            }
            $queueNumbersStr = implode(", ", $projectedQueueNumbers);
            
            $output[] = "  Collection point: {$servicePoint->name}\n";
            $output[] = "  Expected queue positions: {$queueNumbersStr}\n\n";

            // STEP 4: Process Payment (Call YoAPI)
            $output[] = "STEP 4: SEND PAYMENT REQUEST\n";
            $output[] = "----------------------------\n";
            
            // Prepare concise description for transaction (keep under 255 chars)
            $paymentNarrative = "Order " . $invoice->invoice_number . " - " . count($invoiceItems) . " items";
            
            $output[] = "Sending payment prompt to customer...\n";
            $output[] = "  Subtotal: " . number_format($totalAmount) . " UGX\n";
            if ($serviceChargeAmount > 0) {
                $output[] = "  Service charge: " . number_format($serviceChargeAmount) . " UGX\n";
            }
            $output[] = "  Total amount: " . number_format($finalAmount) . " UGX\n";
            $output[] = "  Phone: {$client->payment_phone_number}\n";
            $output[] = "  Order: {$invoice->invoice_number}\n\n";
            
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
                    intval($finalAmount),
                    $paymentNarrative
                );
                
                Log::info('YoAPI Payment Result', ['result' => $paymentResult]);
                
                if (isset($paymentResult['Status']) && $paymentResult['Status'] === 'OK') {
                    $output[] = "[OK] Payment prompt sent successfully\n";
                    $output[] = "✓ Customer will receive payment request on their phone\n";
                    $paymentReference = $paymentResult['TransactionReference'] ?? 'YO' . now()->timestamp;
                    $paymentStatus = 'pending';
                } else {
                    $output[] = "[FAILED] Could not send payment prompt\n";
                    $output[] = "✗ Error: " . ($paymentResult['StatusMessage'] ?? 'Unknown error') . "\n";
                    $paymentReference = 'FAILED-' . now()->timestamp;
                    $paymentStatus = 'failed';
                }
            } catch (\Exception $e) {
                $output[] = "[ERROR] Payment Error: " . $e->getMessage() . "\n";
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
            $output[] = "STEP 5: AWAITING AUTOMATIC PAYMENT CONFIRMATION\n";
            $output[] = "-------------------------------------\n";
            
            if ($paymentStatus === 'pending') {
                $output[] = "[OK] Payment prompt sent to {$client->payment_phone_number}\n";
                $output[] = "Customer will see the payment confirmation on their phone.\n\n";
                
                $output[] = "What happens next:\n";
                $output[] = "  1. Customer confirms the payment on their phone\n";
                $output[] = "  2. System checks for payment confirmation every minute\n";
                $output[] = "  3. Once confirmed:\n";
                $output[] = "     • Payment will be marked as completed\n";
                $output[] = "     • Items will be queued to " . $servicePoint->name . "\n";
                $output[] = "     • Customer's account will be updated\n\n";
            } else {
                $output[] = "[FAILED] Payment could not be sent\n";
                $output[] = "  Status: " . ucfirst($paymentStatus) . "\n";
                $output[] = "  Check YoAPI configuration and try again\n\n";
            }

            // SUMMARY
            $output[] = "====================================================\n";
            if ($paymentStatus === 'pending') {
                $output[] = "[SUCCESS] TEST COMPLETED - ORDER READY FOR PAYMENT\n";
                $output[] = "====================================================\n\n";
                
                $output[] = "CUSTOMER INFORMATION:\n";
                $output[] = "  Name: {$client->name}\n";
                $output[] = "  Phone: {$client->phone_number}\n";
                $output[] = "  Payment Phone: {$client->payment_phone_number}\n\n";
                
                $output[] = "INVOICE SUMMARY:\n";
                $output[] = "  Order #: {$invoice->invoice_number}\n";
                $output[] = "  Collection point: {$servicePoint->name}\n\n";
                
                $output[] = "Items ordered:\n";
                foreach ($invoiceItems as $item) {
                    $output[] = "  • {$item['name']} - {$item['quantity']} × " . number_format($item['unit_price']) . " = " . number_format($item['total']) . " UGX\n";
                }
                
                $output[] = "\n";
                $output[] = "Amount breakdown:\n";
                $output[] = "  Subtotal: " . number_format($totalAmount) . " UGX\n";
                if ($serviceChargeAmount > 0) {
                    $output[] = "  Service charge: " . number_format($serviceChargeAmount) . " UGX\n";
                    $output[] = "  " . str_repeat("-", 40) . "\n";
                    $output[] = "  Total to pay: " . number_format($finalAmount) . " UGX\n\n";
                } else {
                    $output[] = "  " . str_repeat("-", 40) . "\n";
                    $output[] = "  Total to pay: " . number_format($finalAmount) . " UGX\n\n";
                }
                
                $output[] = "NEXT STEPS:\n";
                $output[] = "  ✓ Payment prompt sent to customer\n";
                $output[] = "  → Waiting for customer to confirm payment\n";
                $output[] = "  → Once confirmed, items will be queued automatically\n\n";
                
                $output[] = "TECHNICAL DETAILS (for monitoring):\n";
                $output[] = "  Reference: {$invoice->invoice_number}\n";
                $output[] = "  Transaction ID: {$transaction->id}\n";
                $output[] = "  Client ID: {$client->client_id}\n";
                $output[] = "  Collection Point: {$servicePoint->name}\n";
            } else {
                $output[] = "[FAILURE] TEST FAILED - PAYMENT ERROR\n";
                $output[] = "====================================================\n\n";
                $output[] = "Order could not be submitted:\n";
                $output[] = "  Customer: {$client->name}\n";
                $output[] = "  Order #: {$invoice->invoice_number}\n";
                $output[] = "  Error: Payment initiation failed\n";
                $output[] = "  Amount: " . number_format($totalAmount) . " UGX\n\n";
                
                $output[] = "ACTION REQUIRED:\n";
                $output[] = "  Please check payment configuration and try again\n";
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'output' => implode('', $output)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $output[] = "\n[ERROR] TEST FAILED\n";
            $output[] = "Error: " . $e->getMessage() . "\n";

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'output' => implode('', $output)
            ], 500);
        }
    }
}


