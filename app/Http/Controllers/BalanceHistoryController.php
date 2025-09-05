<?php

namespace App\Http\Controllers;

use App\Models\BalanceHistory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceHistoryController extends Controller
{
    /**
     * Display balance statement for all clients or a specific client
     */
    public function index(Request $request)
    {
        // Show all balance histories for the current business
        $businessId = Auth::user()->business_id;
        $balanceHistories = BalanceHistory::where('business_id', $businessId)
            ->with(['client', 'user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance-statement.index', compact('balanceHistories'));
    }

    /**
     * Show balance statement for a specific client
     */
    public function show($clientId)
    {
        $client = Client::findOrFail($clientId);
        
        $balanceHistories = BalanceHistory::where('client_id', $clientId)
            ->with(['user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance-statement.show', compact('balanceHistories', 'client'));
    }

    /**
     * Get balance statement as JSON for AJAX requests
     */
    public function getBalanceHistory(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
        
        $balanceHistories = BalanceHistory::where('client_id', $clientId)
            ->with(['user', 'invoice', 'business', 'branch'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'date' => $history->created_at->format('Y-m-d H:i:s'),
                    'transaction_type' => $history->transaction_type,
                    'description' => $history->description,
                    'previous_balance' => number_format($history->previous_balance, 2),
                    'change_amount' => $history->getFormattedChangeAmount(),
                    'new_balance' => number_format($history->new_balance, 2),
                    'reference_number' => $history->reference_number,
                    'user_name' => $history->user ? $history->user->name : 'System',
                    'payment_method' => $history->payment_method,
                    'notes' => $history->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $balanceHistories,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'current_balance' => number_format($client->balance ?? 0, 2),
            ]
        ]);
    }




}
