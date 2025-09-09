<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceDeliveryQueue;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Business;
use App\Models\BalanceHistory;
use App\Models\MoneyAccount;

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
                        Log::info('=== STATEMENTS CLEARING DEBUG START ===');
                        
                        // Check if BalanceHistory model exists
                        Log::info('Checking BalanceHistory model...');
                        $modelExists = class_exists('App\Models\BalanceHistory');
                        Log::info('BalanceHistory model exists: ' . ($modelExists ? 'YES' : 'NO'));
                        
                        if (!$modelExists) {
                            throw new \Exception('BalanceHistory model not found');
                        }
                        
                        // Check table exists
                        Log::info('Checking balance_histories table...');
                        $tableExists = Schema::hasTable('balance_histories');
                        Log::info('balance_histories table exists: ' . ($tableExists ? 'YES' : 'NO'));
                        
                        if (!$tableExists) {
                            throw new \Exception('balance_histories table not found');
                        }
                        
                        // Get count
                        Log::info('Getting BalanceHistory count...');
                        $count = BalanceHistory::count();
                        Log::info('BalanceHistory count: ' . $count);
                        
                        // Try to get a sample record first
                        Log::info('Getting sample BalanceHistory record...');
                        $sampleRecord = BalanceHistory::first();
                        Log::info('Sample record: ' . ($sampleRecord ? 'Found' : 'None'));
                        
                        // Log database connection info
                        Log::info('Database connection: ' . DB::connection()->getDatabaseName());
                        
                        // Try truncate
                        Log::info('About to truncate BalanceHistory...');
                        BalanceHistory::truncate();
                        Log::info('Successfully truncated BalanceHistory');
                        
                        $message = "Cleared {$count} balance history records for all users";
                        Log::info('=== STATEMENTS CLEARING DEBUG END - SUCCESS ===');
                        
                    } catch (\Exception $e) {
                        Log::error('=== STATEMENTS CLEARING DEBUG END - ERROR ===', [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                            'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
                        ]);
                        
                        // Also add a dd for immediate debugging
                        Log::error('DD DEBUG INFO', [
                            'error_class' => get_class($e),
                            'error_code' => $e->getCode(),
                            'database_connection' => DB::connection()->getDatabaseName(),
                            'table_exists' => Schema::hasTable('balance_histories'),
                            'model_exists' => class_exists('App\Models\BalanceHistory')
                        ]);
                        
                        throw $e;
                    }
                    break;

                case 'temp-accounts':
                    try {
                        Log::info('=== TEMP ACCOUNTS CLEARING DEBUG START ===');
                        
                        // Check if MoneyAccount model exists
                        Log::info('Checking MoneyAccount model...');
                        $modelExists = class_exists('App\Models\MoneyAccount');
                        Log::info('MoneyAccount model exists: ' . ($modelExists ? 'YES' : 'NO'));
                        
                        if (!$modelExists) {
                            throw new \Exception('MoneyAccount model not found');
                        }
                        
                        // Check table exists
                        Log::info('Checking money_accounts table...');
                        $tableExists = Schema::hasTable('money_accounts');
                        Log::info('money_accounts table exists: ' . ($tableExists ? 'YES' : 'NO'));
                        
                        if (!$tableExists) {
                            throw new \Exception('money_accounts table not found');
                        }
                        
                        // Get count of suspense accounts before clearing
                        Log::info('Getting MoneyAccount count for suspense accounts...');
                        $suspenseAccounts = MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])->get();
                        $count = $suspenseAccounts->count();
                        Log::info('Suspense accounts count: ' . $count);
                        
                        // Log total balance in suspense accounts
                        $totalBalance = $suspenseAccounts->sum('balance');
                        Log::info('Total balance in suspense accounts: ' . $totalBalance);
                        
                        // Reset all suspense account balances to 0
                        Log::info('About to reset suspense account balances...');
                        MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])
                            ->update(['balance' => 0]);
                        
                        Log::info('Successfully reset suspense account balances');
                        $message = "Cleared temporary accounts for {$count} suspense accounts (Total: {$totalBalance})";
                        Log::info('=== TEMP ACCOUNTS CLEARING DEBUG END - SUCCESS ===');
                        
                    } catch (\Exception $e) {
                        Log::error('=== TEMP ACCOUNTS CLEARING DEBUG END - ERROR ===', [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                            'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
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
