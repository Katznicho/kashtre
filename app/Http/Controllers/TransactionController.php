<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Item;
use App\Models\BranchItemPrice;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('transactions.index');
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
    public function show(string $id)
    {
        //
        return view('transactions.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        return view('transactions.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Show the item selection page for a client
     */
    public function itemSelection(Client $client)
    {
        // Check if user has access to this client
        $user = auth()->user();
        if ($client->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to client.');
        }

        // Get current branch
        $currentBranch = $user->currentBranch;

        // Fetch items that belong to this hospital/business
        // If user is from Kashtre (business_id 1), they can access items from all businesses
        if ($user->business_id == 1) {
            $items = Item::orderBy('name')->get();
        } else {
            $items = Item::where('business_id', $user->business_id)
                        ->orderBy('name')
                        ->get();
        }

        // Get branch-specific prices for each item
        // For each item, we need to find the appropriate branch price based on the item's business
        $branchPrices = [];
        
        // Get all branch prices for items from all businesses
        $allBranchPrices = BranchItemPrice::with('branch')
            ->get()
            ->groupBy('item_id');
        
        // For each item, find the appropriate branch price
        foreach ($allBranchPrices as $itemId => $itemBranchPrices) {
            // Find the item to determine its business
            $item = $items->where('id', $itemId)->first();
            if (!$item) continue;
            
            // If user is from Kashtre (business_id 1), they can use any branch price
            // Otherwise, use branch prices from the item's business
            if ($user->business_id == 1) {
                // For Kashtre users, prefer the current branch if it has a price, otherwise use any available price
                $preferredPrice = $itemBranchPrices->where('branch_id', $currentBranch->id)->first();
                if (!$preferredPrice) {
                    $preferredPrice = $itemBranchPrices->first();
                }
            } else {
                // For non-Kashtre users, prefer the current branch if it has a price for this item's business,
                // otherwise use any available price from the item's business
                $businessBranchPrices = $itemBranchPrices->where('branch.business_id', $item->business_id);
                $preferredPrice = $businessBranchPrices->where('branch_id', $currentBranch->id)->first();
                if (!$preferredPrice) {
                    $preferredPrice = $businessBranchPrices->first();
                }
            }
            
            if ($preferredPrice) {
                $branchPrices[$itemId] = $preferredPrice->price;
            }
        }

        // Add branch price or default price to each item
        $items->each(function ($item) use ($branchPrices, $currentBranch, $user) {
            // Ensure we have a valid default price
            $defaultPrice = $item->default_price ?? 0;
            
            // Get branch price if available
            $branchPrice = $branchPrices[$item->id] ?? null;
            
            // Set final price - prefer branch price, fallback to default price
            $item->final_price = $branchPrice ?? $defaultPrice;
            
            // Ensure final_price is never null or empty
            if (empty($item->final_price) || $item->final_price === null) {
                $item->final_price = 0;
            }
            
            // Debug logging for pricing issues
            \Illuminate\Support\Facades\Log::info("=== POS ITEM PRICING DEBUG ===", [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'default_price' => $defaultPrice,
                'branch_price' => $branchPrice,
                'final_price' => $item->final_price,
                'branch_id' => $currentBranch ? $currentBranch->id : 'null',
                'branch_name' => $currentBranch ? $currentBranch->name : 'null',
                'has_branch_price' => isset($branchPrices[$item->id]),
                'business_id' => $user->business_id
            ]);
        });

        // Get ordered items for this client (same logic as service point client details)
        \Illuminate\Support\Facades\Log::info("=== POS ITEM SELECTION - FETCHING ORDERED ITEMS ===", [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'business_id' => $client->business_id,
            'timestamp' => now()->toDateTimeString()
        ]);

        $clientItems = \App\Models\ServiceDeliveryQueue::where('client_id', $client->id)
            ->with(['item', 'invoice', 'startedByUser'])
            ->get();

        \Illuminate\Support\Facades\Log::info("=== POS ITEM SELECTION - ORDERED ITEMS FETCHED ===", [
            'client_id' => $client->id,
            'total_items_found' => $clientItems->count(),
            'items_by_status' => $clientItems->groupBy('status')->map->count(),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Group items by status (ignore completed items)
        $pendingItems = $clientItems->where('status', 'pending');
        $partiallyDoneItems = $clientItems->where('status', 'partially_done');
        // Note: We ignore completed items, same as client details page

        // Calculate correct total amount (only pending and in-progress)
        $correctTotalAmount = $pendingItems->sum(function ($item) {
            return $item->price * $item->quantity;
        }) + $partiallyDoneItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        \Illuminate\Support\Facades\Log::info("=== POS ITEM SELECTION - CALCULATIONS COMPLETE ===", [
            'client_id' => $client->id,
            'pending_items_count' => $pendingItems->count(),
            'partially_done_items_count' => $partiallyDoneItems->count(),
            'completed_items_ignored' => $clientItems->where('status', 'completed')->count(),
            'correct_total_amount' => $correctTotalAmount,
            'unified_component_data' => [
                'pending_items' => $pendingItems->count(),
                'partially_done_items' => $partiallyDoneItems->count(),
                'completed_items' => 0, // Always 0 - ignored
                'total_amount' => $correctTotalAmount
            ],
            'timestamp' => now()->toDateTimeString()
        ]);

        // Determine service point from the client's pending items
        // Use the service_point_id from the first pending or in-progress item
        $firstItem = $clientItems->whereIn('status', ['pending', 'partially_done'])->first();
        $servicePoint = $firstItem ? $firstItem->service_point_id : null;
        
        // If we still don't have a service point, use a default one or the user's first assigned service point
        if (!$servicePoint && $user->service_points && is_array($user->service_points) && count($user->service_points) > 0) {
            $servicePoint = $user->service_points[0];
        }
        
        return view('pos.item-selection', compact(
            'client', 
            'items', 
            'pendingItems', 
            'partiallyDoneItems', 
            'correctTotalAmount',
            'servicePoint'
        ));
    }
}
