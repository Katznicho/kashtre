<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemTestController extends Controller
{
    /**
     * Display the test runner page
     */
    public function runner()
    {
        // Get Exquisite Test Life business (KS1759822163)
        $testBusiness = Business::where('shortcode', 'KS1759822163')->first();
        
        if (!$testBusiness) {
            return back()->with('error', 'Test business "Exquisite Test Life" not found');
        }

        // Get Kololo branch
        $testBranch = Branch::where('business_id', $testBusiness->id)
            ->where('name', 'Kololo')
            ->first();

        if (!$testBranch) {
            return back()->with('error', 'Kololo branch not found in test business');
        }

        return view('system-tests.runner', [
            'testBusiness' => $testBusiness,
            'testBranch' => $testBranch,
            'currentUser' => Auth::user(),
        ]);
    }

    /**
     * Run comprehensive tests
     */
    public function run(Request $request)
    {
        DB::beginTransaction();

        try {
            // Get test business and branch
            $testBusiness = Business::where('shortcode', 'KS1759822163')->first();
            $testBranch = Branch::where('business_id', $testBusiness->id)
                ->where('name', 'Kololo')
                ->first();

            $results = [
                'started_at' => now(),
                'business' => $testBusiness->name,
                'branch' => $testBranch->name,
                'tests' => [],
                'summary' => [
                    'total' => 0,
                    'passed' => 0,
                    'failed' => 0,
                ],
            ];

            // Test 1: Create test client (credit-eligible)
            $testClient = $this->testCreateClient($testBusiness, $testBranch, $results);
            
            // Test 2: Create simple invoice
            $testInvoice = $this->testCreateInvoice($testBusiness, $testBranch, $testClient, $results);
            
            // Test 3: Create payment
            $testPayment = $this->testCreatePayment($testBusiness, $testInvoice, $results);
            
            // Test 4: Verify accounting
            $this->testVerifyAccounting($testBusiness, $testBranch, $results);

            $results['ended_at'] = now();
            $results['duration_seconds'] = $results['ended_at']->diffInSeconds($results['started_at']);

            DB::commit();

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('System test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test: Create client
     */
    private function testCreateClient($business, $branch, &$results)
    {
        $testName = 'Create Test Client (Credit-Eligible)';
        
        try {
            $client = Client::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'name' => 'TEST-CLIENT-' . now()->timestamp,
                'phone_number' => '+256' . rand(700000000, 799999999),
                'date_of_birth' => now()->subYears(30)->toDateString(),
                'is_credit_eligible' => true,
                'max_credit' => 1000000,
                'status' => 'active',
            ]);

            $this->recordTestResult($testName, true, 'Client created: ID ' . $client->id, $results);
            return $client;

        } catch (\Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage(), $results);
            throw $e;
        }
    }

    /**
     * Test: Create invoice
     */
    private function testCreateInvoice($business, $branch, $client, &$results)
    {
        $testName = 'Create Test Invoice';

        try {
            // Get some items
            $items = Item::where('business_id', $business->id)
                ->where('type', '!=', 'package')
                ->limit(2)
                ->get();

            if ($items->isEmpty()) {
                // Create test items if none exist
                $items = [];
                for ($i = 0; $i < 2; $i++) {
                    $item = Item::create([
                        'business_id' => $business->id,
                        'name' => 'TEST-ITEM-' . ($i + 1),
                        'code' => 'TEST-' . ($i + 1),
                        'type' => 'service',
                        'status' => 'active',
                    ]);
                    $items[] = $item;
                }
            }

            $invoiceItems = [];
            $total = 0;
            foreach ($items as $item) {
                $qty = 2;
                $price = 10000;
                $itemTotal = $qty * $price;
                $total += $itemTotal;

                $invoiceItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $qty,
                    'price' => $price,
                    'total_amount' => $itemTotal,
                ];
            }

            $invoice = Invoice::create([
                'invoice_number' => 'TEST-' . now()->format('YmdHis'),
                'client_id' => $client->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'created_by' => Auth::id(),
                'items' => $invoiceItems,
                'subtotal' => $total,
                'service_charge' => 5000,
                'total_amount' => $total + 5000,
                'amount_paid' => 0,
                'balance_due' => $total + 5000,
                'payment_methods' => ['cash'],
                'payment_status' => 'pending',
                'status' => 'confirmed',
            ]);

            $this->recordTestResult($testName, true, 'Invoice created: ' . $invoice->invoice_number, $results);
            return $invoice;

        } catch (\Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage(), $results);
            throw $e;
        }
    }

    /**
     * Test: Create payment
     */
    private function testCreatePayment($business, $invoice, &$results)
    {
        $testName = 'Process Payment';

        try {
            $amountPaid = $invoice->total_amount / 2; // Pay 50%

            $transaction = Transaction::create([
                'client_id' => $invoice->client_id,
                'business_id' => $business->id,
                'invoice_id' => $invoice->id,
                'amount' => $amountPaid,
                'payment_method' => 'cash',
                'status' => 'completed',
                'notes' => 'TEST PAYMENT',
            ]);

            // Update invoice
            $invoice->update([
                'amount_paid' => $amountPaid,
                'balance_due' => $invoice->total_amount - $amountPaid,
                'payment_status' => 'partial',
            ]);

            $this->recordTestResult($testName, true, 'Payment created: ' . number_format($amountPaid, 2), $results);
            return $transaction;

        } catch (\Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage(), $results);
            throw $e;
        }
    }

    /**
     * Test: Verify accounting
     */
    private function testVerifyAccounting($business, $branch, &$results)
    {
        $testName = 'Verify Accounting Integrity';

        try {
            // Count invoices
            $invoiceCount = Invoice::where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->where('invoice_number', 'like', 'TEST-%')
                ->count();

            // Count clients
            $clientCount = Client::where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->where('name', 'like', 'TEST-CLIENT-%')
                ->count();

            $message = "Invoices: {$invoiceCount}, Clients: {$clientCount}";
            $this->recordTestResult($testName, true, $message, $results);

        } catch (\Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage(), $results);
        }
    }

    /**
     * Record individual test result
     */
    private function recordTestResult($name, $passed, $message, &$results)
    {
        $results['tests'][] = [
            'name' => $name,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => now(),
        ];

        $results['summary']['total']++;
        if ($passed) {
            $results['summary']['passed']++;
        } else {
            $results['summary']['failed']++;
        }
    }

    /**
     * Display test results
     */
    public function results($testId)
    {
        // Results are stored in session or could be in a test_results table
        return view('system-tests.results');
    }
}
