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
        // Only allow admin users (business_id = 1) with active status
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            // Check if user is authenticated
            if (!$user) {
                abort(401, 'Authentication required.');
            }
            
            // Check if user is admin (business_id = 1)
            if ($user->business_id != 1) {
                abort(403, 'Unauthorized access. Admin privileges required.');
            }
            
            // Check if user is active
            if ($user->status !== 'active') {
                abort(403, 'Account is not active. Please contact administrator.');
            }
            
            // Additional security: Check if user has admin permissions
            if (!$user->permissions || !is_array($user->permissions)) {
                abort(403, 'Insufficient permissions. Admin access required.');
            }
            
            return $next($request);
        });
    }

    public function clearData(Request $request)
    {
        try {
            // Additional security check at method level
            $user = auth()->user();
            if (!$user || $user->business_id != 1 || $user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }

            $type = $request->input('type');
            $count = 0;
            $message = '';

            // Validate input
            $allowedTypes = ['queues', 'transactions', 'client-balances', 'kashtre-balance', 'business-balances', 'statements'];
            if (!in_array($type, $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data type specified'
                ], 400);
            }

            Log::info('=== TESTING: Clear Data Started ===', [
                'type' => $type,
                'user_id' => auth()->id(),
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            switch ($type) {
                case 'queues':
                    try {
                        $count = ServiceDeliveryQueue::count();
                        Log::info('About to truncate ServiceDeliveryQueue', ['count' => $count]);
                        ServiceDeliveryQueue::truncate();
                        $message = "Cleared {$count} service delivery queue records";
                        Log::info('Successfully truncated ServiceDeliveryQueue', ['count' => $count]);
                    } catch (\Exception $e) {
                        Log::error('Error truncating ServiceDeliveryQueue', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;

                case 'transactions':
                    try {
                        $count = Transaction::count();
                        Log::info('About to truncate Transaction', ['count' => $count]);
                        Transaction::truncate();
                        $message = "Cleared {$count} transaction records";
                        Log::info('Successfully truncated Transaction', ['count' => $count]);
                    } catch (\Exception $e) {
                        Log::error('Error truncating Transaction', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;

                case 'client-balances':
                    try {
                        // Reset all client balances to 0
                        $clients = Client::all();
                        Log::info('About to reset client balances', ['count' => $clients->count()]);
                        foreach ($clients as $client) {
                            $client->update(['balance' => 0]);
                        }
                        $count = $clients->count();
                        $message = "Reset balance for {$count} clients to 0";
                        Log::info('Successfully reset client balances', ['count' => $count]);
                    } catch (\Exception $e) {
                        Log::error('Error resetting client balances', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;

                case 'kashtre-balance':
                    try {
                        // Reset Kashtre business balance (business_id = 1)
                        $kashtreBusiness = Business::find(1);
                        if ($kashtreBusiness) {
                            Log::info('About to reset Kashtre business balance');
                            $kashtreBusiness->update(['balance' => 0]);
                            $count = 1;
                            $message = "Reset Kashtre business balance to 0";
                            Log::info('Successfully reset Kashtre business balance');
                        } else {
                            $message = "Kashtre business not found";
                            Log::warning('Kashtre business not found');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error resetting Kashtre business balance', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;

                case 'business-balances':
                    try {
                        // Reset all business balances to 0
                        $businesses = Business::all();
                        Log::info('About to reset business balances', ['count' => $businesses->count()]);
                        foreach ($businesses as $business) {
                            $business->update(['balance' => 0]);
                        }
                        $count = $businesses->count();
                        $message = "Reset balance for {$count} businesses to 0";
                        Log::info('Successfully reset business balances', ['count' => $count]);
                    } catch (\Exception $e) {
                        Log::error('Error resetting business balances', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;

                case 'statements':
                    try {
                        $count = BalanceHistory::count();
                        Log::info('About to truncate BalanceHistory', ['count' => $count]);
                        BalanceHistory::truncate();
                        $message = "Cleared {$count} balance history records for all users";
                        Log::info('Successfully truncated BalanceHistory', ['count' => $count]);
                    } catch (\Exception $e) {
                        Log::error('Error truncating BalanceHistory', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
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
