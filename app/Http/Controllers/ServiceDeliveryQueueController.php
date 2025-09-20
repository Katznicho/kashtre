<?php

namespace App\Http\Controllers;

use App\Models\ServiceDeliveryQueue;
use App\Models\Business;
use App\Models\ContractorProfile;
use App\Services\MoneyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ServiceDeliveryQueueController extends Controller
{
    /**
     * Move an item to partially done status
     */
    public function moveToPartiallyDone(ServiceDeliveryQueue $serviceDeliveryQueue)
    {
        try {
            // Check if user has access to this service point
            $user = auth()->user();
            $servicePoint = $serviceDeliveryQueue->servicePoint;
            
            if (!$this->userHasAccessToServicePoint($user, $servicePoint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this service point.'
                ], 403);
            }

            // Log the action before processing money transfers
            Log::info("=== MARKING ITEM AS PARTIALLY DONE ===", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'item_id' => $serviceDeliveryQueue->item_id,
                'item_name' => $serviceDeliveryQueue->item_name,
                'invoice_id' => $serviceDeliveryQueue->invoice_id,
                'invoice_number' => $serviceDeliveryQueue->invoice->invoice_number ?? 'N/A',
                'client_id' => $serviceDeliveryQueue->client_id,
                'client_name' => $serviceDeliveryQueue->client->name ?? 'N/A',
                'business_id' => $serviceDeliveryQueue->business_id,
                'service_point_id' => $serviceDeliveryQueue->service_point_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_status' => $serviceDeliveryQueue->status,
                'new_status' => 'partially_done',
                'timestamp' => now()->toISOString()
            ]);

            // Process money transfers
            $moneyTrackingService = app(MoneyTrackingService::class);
            $moneyTrackingService->processServiceDeliveryMoneyTransfer($serviceDeliveryQueue, $user);

            // Update the item status
            $serviceDeliveryQueue->markAsPartiallyDone($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Item moved to partially done successfully. Money transfers processed.',
                'data' => [
                    'id' => $serviceDeliveryQueue->id,
                    'status' => $serviceDeliveryQueue->status,
                    'partially_done_at' => $serviceDeliveryQueue->partially_done_at
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to move item to partially done', [
                'item_id' => $serviceDeliveryQueue->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to move item to partially done. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move item to completed status
     */
    public function moveToCompleted(ServiceDeliveryQueue $serviceDeliveryQueue)
    {
        try {
            // Check if user has access to this service point
            $user = Auth::user();
            $servicePoint = $serviceDeliveryQueue->servicePoint;
            
            if (!$this->userHasAccessToServicePoint($user, $servicePoint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this service point.'
                ], 403);
            }

            // Log the action before processing money transfers
            Log::info("=== MARKING ITEM AS COMPLETED ===", [
                'service_delivery_queue_id' => $serviceDeliveryQueue->id,
                'item_id' => $serviceDeliveryQueue->item_id,
                'item_name' => $serviceDeliveryQueue->item_name,
                'item_type' => $serviceDeliveryQueue->item->type ?? 'unknown',
                'invoice_id' => $serviceDeliveryQueue->invoice_id,
                'invoice_number' => $serviceDeliveryQueue->invoice->invoice_number ?? 'N/A',
                'invoice_package_adjustment' => $serviceDeliveryQueue->invoice->package_adjustment ?? 0,
                'client_id' => $serviceDeliveryQueue->client_id,
                'client_name' => $serviceDeliveryQueue->client->name ?? 'N/A',
                'business_id' => $serviceDeliveryQueue->business_id,
                'service_point_id' => $serviceDeliveryQueue->service_point_id,
                'queue_price' => $serviceDeliveryQueue->price,
                'queue_quantity' => $serviceDeliveryQueue->quantity,
                'calculated_item_amount' => $serviceDeliveryQueue->price * $serviceDeliveryQueue->quantity,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_status' => $serviceDeliveryQueue->status,
                'new_status' => 'completed',
                'timestamp' => now()->toISOString()
            ]);

            // Process money transfers (including package adjustments)
            $moneyTrackingService = app(MoneyTrackingService::class);
            $moneyTrackingService->processServiceDeliveryMoneyTransfer($serviceDeliveryQueue, $user);

            // Update the item status
            $serviceDeliveryQueue->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'Item marked as completed successfully. Money transfers processed.',
                'item' => $serviceDeliveryQueue->fresh()
            ]);
        } catch (Exception $e) {
            Log::error('ServiceDeliveryQueueController@moveToCompleted error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the item.'
            ], 500);
        }
    }

    /**
     * Get all items for a service point
     */
    public function getServicePointItems($servicePointId)
    {
        try {
            $user = Auth::user();
            
            // Check if user has access to this service point
            if (!$this->userHasAccessToServicePoint($user, $servicePointId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this service point.'
                ], 403);
            }

            $items = ServiceDeliveryQueue::where('service_point_id', $servicePointId)
                ->with(['client', 'invoice'])
                ->orderBy('queued_at')
                ->get();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (Exception $e) {
            Log::error('ServiceDeliveryQueueController@getServicePointItems error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching items.'
            ], 500);
        }
    }

    /**
     * Show all pending items for a service point
     */
    public function showPendingItems($servicePointId)
    {
        try {
            $user = Auth::user();
            $servicePoint = \App\Models\ServicePoint::findOrFail($servicePointId);
            
            // Check if user has access to this service point
            if (!$this->userHasAccessToServicePoint($user, $servicePoint)) {
                abort(403, 'You do not have access to this service point.');
            }

            $pendingItems = ServiceDeliveryQueue::where('service_point_id', $servicePointId)
                ->where('status', 'pending')
                ->with(['client', 'invoice'])
                ->orderBy('queued_at')
                ->paginate(50);

            return view('service-delivery-queue.pending', compact('pendingItems', 'servicePoint'));
        } catch (Exception $e) {
            Log::error('ServiceDeliveryQueueController@showPendingItems error: ' . $e->getMessage());
            abort(500, 'An error occurred while fetching pending items.');
        }
    }

    /**
     * Show all completed items for a service point
     */
    public function showCompletedItems($servicePointId)
    {
        try {
            $user = Auth::user();
            $servicePoint = \App\Models\ServicePoint::findOrFail($servicePointId);
            
            // Check if user has access to this service point
            if (!$this->userHasAccessToServicePoint($user, $servicePoint)) {
                abort(403, 'You do not have access to this service point.');
            }

            $completedItems = ServiceDeliveryQueue::where('service_point_id', $servicePointId)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->with(['client', 'invoice'])
                ->orderBy('completed_at', 'desc')
                ->paginate(50);

            return view('service-delivery-queue.completed', compact('completedItems', 'servicePoint'));
        } catch (Exception $e) {
            Log::error('ServiceDeliveryQueueController@showCompletedItems error: ' . $e->getMessage());
            abort(500, 'An error occurred while fetching completed items.');
        }
    }

    /**
     * Reset all queues for a service point (for testing purposes)
     */
    public function resetServicePointQueues($servicePointId)
    {
        try {
            $user = Auth::user();
            $servicePoint = \App\Models\ServicePoint::findOrFail($servicePointId);
            
            // Check if user has access to this service point
            if (!$this->userHasAccessToServicePoint($user, $servicePoint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this service point.'
                ], 403);
            }

            // Get count of items that will be reset
            $pendingCount = ServiceDeliveryQueue::where('service_point_id', $servicePointId)
                ->whereIn('status', ['pending', 'in_progress', 'partially_done'])
                ->count();

            // Reset all pending, in-progress, and partially done items
            ServiceDeliveryQueue::where('service_point_id', $servicePointId)
                ->whereIn('status', ['pending', 'in_progress', 'partially_done'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully reset {$pendingCount} queued items for {$servicePoint->name}",
                'data' => [
                    'service_point_id' => $servicePointId,
                    'service_point_name' => $servicePoint->name,
                    'items_reset' => $pendingCount
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ServiceDeliveryQueueController@resetServicePointQueues error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting the queues.'
            ], 500);
        }
    }


    /**
     * Check if user has access to a service point
     */
    private function userHasAccessToServicePoint($user, $servicePoint)
    {
        if (is_numeric($servicePoint)) {
            $servicePoint = \App\Models\ServicePoint::find($servicePoint);
        }
        
        if (!$servicePoint) {
            return false;
        }

        // Check if user has service_points assigned
        if (!$user->service_points) {
            return false;
        }

        // Check if the service point is in user's assigned service points
        return in_array($servicePoint->id, $user->service_points);
    }
}
