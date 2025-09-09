<?php

namespace App\Http\Controllers;

use App\Models\ServicePoint;
use Illuminate\Http\Request;

class ServicePointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("service_points.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ServicePoint $servicePoint)
    {
        // Check if user has access to this service point
        $user = auth()->user();
        
        if (!$user->service_points || !in_array($servicePoint->id, $user->service_points)) {
            abort(403, 'You do not have access to this service point.');
        }

        // Load the service point with its queues and related data
        $servicePoint->load([
            'pendingDeliveryQueues.client',
            'pendingDeliveryQueues.invoice',
            'pendingDeliveryQueues.item',
            'partiallyDoneDeliveryQueues.client', 
            'partiallyDoneDeliveryQueues.invoice',
            'partiallyDoneDeliveryQueues.item',
            'partiallyDoneDeliveryQueues.startedByUser',
            'serviceDeliveryQueues' => function($query) {
                $query->where('status', 'completed')
                      ->whereDate('completed_at', today())
                      ->with(['client', 'invoice', 'item']);
            }
        ]);

        // Group queues by client for better organization
        $clientsWithItems = [];
        
        // Process pending items
        foreach ($servicePoint->pendingDeliveryQueues as $queue) {
            $clientId = $queue->client_id;
            if (!isset($clientsWithItems[$clientId])) {
                $clientsWithItems[$clientId] = [
                    'client' => $queue->client,
                    'pending' => [],
                    'partially_done' => [],
                    'completed' => []
                ];
            }
            $clientsWithItems[$clientId]['pending'][] = $queue;
        }
        
        // Process partially done items
        foreach ($servicePoint->partiallyDoneDeliveryQueues as $queue) {
            $clientId = $queue->client_id;
            if (!isset($clientsWithItems[$clientId])) {
                $clientsWithItems[$clientId] = [
                    'client' => $queue->client,
                    'pending' => [],
                    'partially_done' => [],
                    'completed' => []
                ];
            }
            $clientsWithItems[$clientId]['partially_done'][] = $queue;
        }
        
        // Process completed items
        foreach ($servicePoint->serviceDeliveryQueues as $queue) {
            $clientId = $queue->client_id;
            if (!isset($clientsWithItems[$clientId])) {
                $clientsWithItems[$clientId] = [
                    'client' => $queue->client,
                    'pending' => [],
                    'partially_done' => [],
                    'completed' => []
                ];
            }
            $clientsWithItems[$clientId]['completed'][] = $queue;
        }

        return view('service-points.show', compact('servicePoint', 'clientsWithItems'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServicePoint $servicePoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServicePoint $servicePoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicePoint $servicePoint)
    {
        //
    }

    /**
     * Show client details page
     */
    public function clientDetails(ServicePoint $servicePoint, $clientId)
    {
        // Check if user has access to this service point
        $user = auth()->user();
        
        if (!$user->service_points || !in_array($servicePoint->id, $user->service_points)) {
            abort(403, 'You do not have access to this service point.');
        }

        // Get client data
        $client = \App\Models\Client::findOrFail($clientId);
        
        // Get all items for this client at this service point
        $clientItems = \App\Models\ServiceDeliveryQueue::where('service_point_id', $servicePoint->id)
            ->where('client_id', $clientId)
            ->with(['item', 'invoice', 'startedByUser'])
            ->get();

        // Group items by status
        $pendingItems = $clientItems->where('status', 'pending');
        $partiallyDoneItems = $clientItems->where('status', 'partially_done');

        // Get client statement (balance statement)
        $clientStatement = \App\Models\BalanceHistory::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get client notes (if you have a notes system)
        $clientNotes = []; // You can implement this based on your notes system

        return view('service-points.client-details', compact(
            'servicePoint', 
            'client', 
            'pendingItems', 
            'partiallyDoneItems', 
            'clientStatement',
            'clientNotes'
        ));
    }

    /**
     * Update statuses and process money movements (Save & Exit)
     */
    public function updateStatusesAndProcessMoneyMovements(Request $request, ServicePoint $servicePoint, $clientId)
    {
        // Start database transaction for rollback capability
        \Illuminate\Support\Facades\DB::beginTransaction();
        
        try {
            $request->validate([
                'item_statuses' => 'required|array',
                'item_statuses.*' => 'required|in:pending,partially_done,completed'
            ]);

            $itemStatuses = $request->input('item_statuses');
            $updatedCount = 0;
            $moneyMovementCount = 0;

            // Initialize MoneyTrackingService
            $moneyTrackingService = new \App\Services\MoneyTrackingService();

            // Get client for statement updates
            $client = \App\Models\Client::find($clientId);
            if (!$client) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            // CRITICAL FIX: Ensure client has a credit record in BalanceHistory before processing debits
            $this->ensureClientCreditRecordExists($client, $moneyTrackingService);

            // Process service charge ONCE per invoice (not per item)
            $invoice = null;
            $serviceChargeProcessed = false;

            // Process each finalized item
            foreach ($itemStatuses as $itemId => $status) {
                $item = \App\Models\ServiceDeliveryQueue::where('id', $itemId)
                    ->where('service_point_id', $servicePoint->id)
                    ->where('client_id', $clientId)
                    ->with(['item', 'invoice'])
                    ->first();

                if ($item && in_array($status, ['partially_done', 'completed'])) {
                    // Check if money was already moved for this item
                    if ($item->is_money_moved) {
                        $item->status = $status;
                        $item->save();
                        $updatedCount++;
                        continue;
                    }
                    
                    // Update the status
                    $item->status = $status;
                    $item->save();

                    // DISABLED: Service charge is now processed in InvoiceController with suspense system
                    // Process service charge ONCE per invoice
                    // if (!$serviceChargeProcessed && $item->invoice) {
                    //     $invoice = $item->invoice;
                    //     $this->processServiceCharge($invoice, $moneyTrackingService, $client);
                    //     $serviceChargeProcessed = true;
                    // }

                    // Process money movements for finalized items
                    try {
                        $this->processItemMoneyMovement($item, $client, $moneyTrackingService);
                        
                        // Mark item as money moved to prevent double processing
                        $item->is_money_moved = true;
                        $item->money_moved_at = now();
                        $item->money_moved_by_user_id = auth()->id();
                        $item->save();
                        
                        $moneyMovementCount++;
                    } catch (\Exception $e) {
                        throw $e;
                    }

                    $updatedCount++;
                }
            }

            $message = "Successfully updated {$updatedCount} items and processed {$moneyMovementCount} money movements";

            // Commit all transactions if everything succeeded
            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'money_movement_count' => $moneyMovementCount
            ]);
            
        } catch (\Exception $e) {
            // Rollback all transactions if any error occurred
            \Illuminate\Support\Facades\DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process money movement for a single completed item
     */
    private function processItemMoneyMovement($item, $client, $moneyTrackingService)
    {
        $invoice = $item->invoice;
        $business = \App\Models\Business::find($invoice->business_id);
        $itemAmount = $item->price * $item->quantity;
        
        try {
            // NEW LOGIC: Use the processSaveAndExit method instead of creating client statements
            // This will move money from suspense account to final accounts without creating client statements
            
            // Prepare item data for processSaveAndExit
            $itemData = [
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'total_amount' => $itemAmount
            ];
            
            // Call the new processSaveAndExit method
            $transferRecords = $moneyTrackingService->processSaveAndExit($invoice, [$itemData]);
            
            \Illuminate\Support\Facades\Log::info("Item money movement processed via processSaveAndExit", [
                'item_id' => $item->item_id,
                'item_amount' => $itemAmount,
                'transfer_records' => $transferRecords
            ]);

            // All money movement and balance statement creation is now handled by processSaveAndExit
            // No additional processing needed here
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Process service charge for an invoice (DEPRECATED - now handled by processSaveAndExit)
     */
    private function processServiceCharge($invoice, $moneyTrackingService, $client)
    {
        // Service charge processing is now handled by MoneyTrackingService::processSaveAndExit
        // This method is kept for backward compatibility but does nothing
        \Illuminate\Support\Facades\Log::info("processServiceCharge called but deprecated - service charges now handled by processSaveAndExit", [
            'invoice_id' => $invoice->id,
            'service_charge' => $invoice->service_charge ?? 0
        ]);
    }

    /**
     * Ensure a client has a credit record in BalanceHistory before processing debits.
     * This is crucial for the suspense balance calculation to work correctly.
     */
    private function ensureClientCreditRecordExists($client, $moneyTrackingService)
    {
        // Check if the client already has a credit record for today
        $today = now()->toDateString();
        $existingCredit = \App\Models\BalanceHistory::where('client_id', $client->id)
            ->where('transaction_type', 'credit')
            ->whereDate('created_at', $today)
            ->first();

        if (!$existingCredit) {
            // Create a credit record for the client for today
            \App\Models\BalanceHistory::recordCredit(
                $client,
                0, // Initial credit amount
                "Initial credit for suspense balance calculation",
                "Suspense Balance",
                "Suspense Balance"
            );
        }
    }
}
