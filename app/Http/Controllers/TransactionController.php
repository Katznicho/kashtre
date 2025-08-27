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
        $items = Item::where('business_id', $user->business_id)
                    ->orderBy('name')
                    ->get();

        // Get branch-specific prices if branch exists
        $branchPrices = [];
        if ($currentBranch) {
            $branchPrices = BranchItemPrice::where('branch_id', $currentBranch->id)
                ->where('business_id', $user->business_id)
                ->pluck('price', 'item_id')
                ->toArray();
        }

        // Add branch price or default price to each item
        $items->each(function ($item) use ($branchPrices) {
            $item->final_price = $branchPrices[$item->id] ?? $item->default_price;
        });

        return view('pos.item-selection', compact('client', 'items'));
    }
}
