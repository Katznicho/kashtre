<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceDeliveryQueue;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Business;
use App\Models\BalanceHistory;

class TestingController extends Controller
{
    public function __construct()
    {
        // Only allow admin users (business_id = 1)
        $this->middleware(function ($request, $next) {
            if (auth()->user()->business_id != 1) {
                abort(403, 'Unauthorized access. Admin privileges required.');
            }
            return $next($request);
        });
    }

    public function clearData(Request $request)
    {
        try {
            $type = $request->input('type');
            $count = 0;
            $message = '';

            Log::info('=== TESTING: Clear Data Started ===', [
                'type' => $type,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            switch ($type) {
                case 'queues':
                    $count = ServiceDeliveryQueue::count();
                    ServiceDeliveryQueue::truncate();
                    $message = "Cleared {$count} service delivery queue records";
                    break;

                case 'transactions':
                    $count = Transaction::count();
                    Transaction::truncate();
                    $message = "Cleared {$count} transaction records";
                    break;

                case 'client-balances':
                    // Reset all client balances to 0
                    $clients = Client::all();
                    foreach ($clients as $client) {
                        $client->update(['balance' => 0]);
                    }
                    $count = $clients->count();
                    $message = "Reset balance for {$count} clients to 0";
                    break;

                case 'kashtre-balance':
                    // Reset Kashtre business balance (business_id = 1)
                    $kashtreBusiness = Business::find(1);
                    if ($kashtreBusiness) {
                        $kashtreBusiness->update(['balance' => 0]);
                        $count = 1;
                        $message = "Reset Kashtre business balance to 0";
                    } else {
                        $message = "Kashtre business not found";
                    }
                    break;

                case 'business-balances':
                    // Reset all business balances to 0
                    $businesses = Business::all();
                    foreach ($businesses as $business) {
                        $business->update(['balance' => 0]);
                    }
                    $count = $businesses->count();
                    $message = "Reset balance for {$count} businesses to 0";
                    break;

                case 'statements':
                    $count = BalanceHistory::count();
                    BalanceHistory::truncate();
                    $message = "Cleared {$count} balance history records for all users";
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid data type specified'
                    ], 400);
            }

            Log::info('=== TESTING: Clear Data Completed ===', [
                'type' => $type,
                'records_affected' => $count,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('=== TESTING: Clear Data Failed ===', [
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear data: ' . $e->getMessage()
            ], 500);
        }
    }
}
