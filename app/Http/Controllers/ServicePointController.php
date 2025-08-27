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
        
        // Check if the service point is in the user's assigned service points
        if (!$user->service_points || !in_array($servicePoint->id, $user->service_points)) {
            abort(403, 'You do not have access to this service point.');
        }

        // Load the service point with its queues and related data
        $servicePoint->load([
            'pendingDeliveryQueues.client',
            'pendingDeliveryQueues.invoice',
            'partiallyDoneDeliveryQueues.client', 
            'partiallyDoneDeliveryQueues.invoice',
            'partiallyDoneDeliveryQueues.startedByUser',
            'serviceDeliveryQueues' => function($query) {
                $query->where('status', 'completed')
                      ->whereDate('completed_at', today())
                      ->with(['client', 'invoice']);
            }
        ]);

        return view('service-points.show', compact('servicePoint'));
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
}
