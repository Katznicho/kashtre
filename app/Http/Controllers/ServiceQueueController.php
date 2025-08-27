<?php

namespace App\Http\Controllers;

use App\Models\ServiceQueue;
use App\Models\ServicePoint;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceQueueController extends Controller
{
    /**
     * Display the service point dashboard
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get service points assigned to the user
            $servicePoints = $user->service_points ? ServicePoint::whereIn('id', $user->service_points)->get() : collect();
            
            return view('service-queues.index', compact('servicePoints'));
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@index error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the service point dashboard.']);
        }
    }

    /**
     * Show the form for creating a new queue entry
     */
    public function create()
    {
        // Add Client functionality will be handled differently
        return redirect()->route('service-queues.index');
    }

    /**
     * Store a newly created queue entry
     */
    public function store(Request $request)
    {
        // Add Client functionality will be handled differently
        return redirect()->route('service-queues.index');
    }

    /**
     * Display the specified queue entry
     */
    public function show(ServiceQueue $serviceQueue)
    {
        try {
            return view('service-queues.show', compact('serviceQueue'));
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@show error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the queue details.']);
        }
    }

    /**
     * Show the form for editing the specified queue entry
     */
    public function edit(ServiceQueue $serviceQueue)
    {
        try {
            $user = Auth::user();
            $servicePoints = $user->service_points ? ServicePoint::whereIn('id', $user->service_points)->get() : collect();
            $clients = Client::where('business_id', $user->business_id)->get();
            
            return view('service-queues.edit', compact('serviceQueue', 'servicePoints', 'clients'));
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@edit error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the edit form.']);
        }
    }

    /**
     * Update the specified queue entry
     */
    public function update(Request $request, ServiceQueue $serviceQueue)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'service_point_id' => 'required|exists:service_points,id',
                'priority' => 'required|in:low,normal,high,urgent',
                'estimated_duration' => 'nullable|integer|min:1',
                'total_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'items' => 'nullable|array',
                'items.*.name' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            $serviceQueue->update($validated);

            return redirect()->route('service-queues.index')
                ->with('success', 'Queue entry updated successfully.');
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@update error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the queue entry.'])->withInput();
        }
    }

    /**
     * Remove the specified queue entry
     */
    public function destroy(ServiceQueue $serviceQueue)
    {
        try {
            $serviceQueue->delete();
            return redirect()->route('service-queues.index')
                ->with('success', 'Queue entry deleted successfully.');
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@destroy error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the queue entry.']);
        }
    }

    /**
     * Start processing a queue entry
     */
    public function startProcessing(ServiceQueue $serviceQueue)
    {
        try {
            $serviceQueue->update([
                'status' => ServiceQueue::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service started successfully.',
                'queue' => $serviceQueue->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@startProcessing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting the service.'
            ], 500);
        }
    }

    /**
     * Complete a queue entry
     */
    public function complete(ServiceQueue $serviceQueue)
    {
        try {
            $serviceQueue->update([
                'status' => ServiceQueue::STATUS_COMPLETED,
                'completed_at' => now(),
                'actual_duration' => $serviceQueue->started_at ? now()->diffInMinutes($serviceQueue->started_at) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service completed successfully.',
                'queue' => $serviceQueue->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@complete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the service.'
            ], 500);
        }
    }

    /**
     * Cancel a queue entry
     */
    public function cancel(ServiceQueue $serviceQueue)
    {
        try {
            $serviceQueue->update([
                'status' => ServiceQueue::STATUS_CANCELLED,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Queue entry cancelled successfully.',
                'queue' => $serviceQueue->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@cancel error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the queue entry.'
            ], 500);
        }
    }

    /**
     * Get statistics for a service point
     */
    public function getStats(ServicePoint $servicePoint)
    {
        try {
            $stats = $servicePoint->queue_stats;
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@getStats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching statistics.'
            ], 500);
        }
    }

    /**
     * Get all queues for a service point
     */
    public function getServicePointQueues(ServicePoint $servicePoint)
    {
        try {
            $queues = $servicePoint->serviceQueues()
                ->with(['client', 'user'])
                ->orderBy('queue_number')
                ->get();
            
            return response()->json([
                'success' => true,
                'queues' => $queues
            ]);
        } catch (\Exception $e) {
            Log::error('ServiceQueueController@getServicePointQueues error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching queues.'
            ], 500);
        }
    }
}
