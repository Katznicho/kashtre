<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ServiceDeliveryQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompletedClientsController extends Controller
{
    /**
     * Display completed clients for the current user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to view clients
        if (!in_array('View Clients', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view clients.');
        }
        
        return view('clients.completed');
    }

    /**
     * Show completed items for a specific client
     */
    public function showCompletedItems(Client $client)
    {
        $user = Auth::user();
        
        // Check if user has permission to view clients
        if (!in_array('View Clients', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view clients.');
        }

        // Get completed items for this client assigned to the current user
        $completedItems = ServiceDeliveryQueue::where('client_id', $client->id)
            ->where('status', 'completed')
            ->where('assigned_to', $user->id)
            ->with(['item', 'invoice', 'servicePoint'])
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('clients.completed-items', compact('client', 'completedItems'));
    }
}

