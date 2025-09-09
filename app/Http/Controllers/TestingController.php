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
use App\Models\PackageTracking;
use App\Models\PackageUsage;

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
        // DEBUG: Log that we're using the latest version
        Log::info('=== TESTING CONTROLLER VERSION CHECK ===', [
            'version' => 'v2.0-with-temp-accounts',
            'timestamp' => now(),
            'user_id' => auth()->id()
        ]);
        
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
            $allowedTypes = ['queues', 'transactions', 'client-balances', 'kashtre-balance', 'business-balances', 'statements', 'client-balance-statements'];
            if (!in_array($type, $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data type specified'
                ], 400);
            }

            Log::info('=== TESTING: Clear Data Started ===', [
                'type' => $type,
                'type_length' => strlen($type),
                'type_encoded' => json_encode($type),
                'user_id' => auth()->id(),
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            // Debug: Log all possible cases
            Log::info('DEBUG: Available cases', [
                'cases' => ['queues', 'transactions', 'client-balances', 'kashtre-balance', 'business-balances', 'statements', 'temp-accounts', 'package-tracking'],
                'received_type' => $type,
                'exact_match' => $type === 'temp-accounts'
            ]);

            switch ($type) {
                case 'simple-test':
                    Log::info('SUCCESS: simple-test case reached!');
                    $message = "SIMPLE TEST CASE WORKING!";
                    $count = 1;
                    break;
                    
                case 'queues':
                    try {
                        $queueCount = ServiceDeliveryQueue::count();
                        Log::info('About to truncate ServiceDeliveryQueue', ['count' => $queueCount]);
                        ServiceDeliveryQueue::truncate();
                        
                        // Also clear package tracking data
                        Log::info('About to clear package tracking data');
                        $packageTrackingCount = 0;
                        $packageMessage = "";
                        
                        // Clear package suspense accounts
                        $packageSuspenseAccounts = MoneyAccount::where('type', 'package_suspense_account')->get();
                        $packageSuspenseCount = $packageSuspenseAccounts->count();
                        $packageSuspenseBalance = $packageSuspenseAccounts->sum('balance');
                        
                        if ($packageSuspenseCount > 0) {
                            MoneyAccount::where('type', 'package_suspense_account')
                                ->update(['balance' => 0]);
                            $packageTrackingCount += $packageSuspenseCount;
                            $packageMessage .= "{$packageSuspenseCount} package suspense accounts (Total: {$packageSuspenseBalance}), ";
                            Log::info('Cleared package suspense accounts', ['count' => $packageSuspenseCount, 'total_balance' => $packageSuspenseBalance]);
                        }
                        
                        // Clear package-related transactions
                        $packageTransactions = Transaction::where('transaction_for', 'package')
                            ->orWhere('description', 'like', '%package%')
                            ->orWhere('description', 'like', '%delivery%')
                            ->get();
                        $packageTransactionCount = $packageTransactions->count();
                        
                        if ($packageTransactionCount > 0) {
                            Transaction::where('transaction_for', 'package')
                                ->orWhere('description', 'like', '%package%')
                                ->orWhere('description', 'like', '%delivery%')
                                ->delete();
                            $packageTrackingCount += $packageTransactionCount;
                            $packageMessage .= "{$packageTransactionCount} package transactions, ";
                            Log::info('Cleared package transactions', ['count' => $packageTransactionCount]);
                        }
                        
                        // Clear package tracking records
                        $packageTrackingRecords = PackageTracking::count();
                        if ($packageTrackingRecords > 0) {
                            PackageTracking::truncate();
                            $packageTrackingCount += $packageTrackingRecords;
                            $packageMessage .= "{$packageTrackingRecords} package tracking records, ";
                            Log::info('Cleared package tracking records', ['count' => $packageTrackingRecords]);
                        }
                        
                        // Clear package usage records
                        $packageUsageRecords = PackageUsage::count();
                        if ($packageUsageRecords > 0) {
                            PackageUsage::truncate();
                            $packageTrackingCount += $packageUsageRecords;
                            $packageMessage .= "{$packageUsageRecords} package usage records, ";
                            Log::info('Cleared package usage records', ['count' => $packageUsageRecords]);
                        }
                        
                        // Remove trailing comma and space
                        $packageMessage = rtrim($packageMessage, ', ');
                        
                        $count = $queueCount + $packageTrackingCount;
                        if ($packageMessage) {
                            $message = "Cleared {$queueCount} service delivery queues and {$packageMessage}";
                        } else {
                            $message = "Cleared {$queueCount} service delivery queue records";
                        }
                        
                        Log::info('Successfully truncated ServiceDeliveryQueue and cleared package tracking', ['queue_count' => $queueCount, 'package_count' => $packageTrackingCount]);
                    } catch (\Exception $e) {
                        Log::error('Error truncating ServiceDeliveryQueue and clearing package tracking', ['error' => $e->getMessage()]);
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
                        $clientCount = $clients->count();
                        
                        // Also clear suspense accounts (temporary accounts)
                        Log::info('About to clear suspense accounts');
                        $suspenseAccounts = MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])->get();
                        $suspenseCount = $suspenseAccounts->count();
                        $totalBalance = $suspenseAccounts->sum('balance');
                        
                        if ($suspenseCount > 0) {
                            MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])
                                ->update(['balance' => 0]);
                            Log::info('Cleared suspense accounts', ['count' => $suspenseCount, 'total_balance' => $totalBalance]);
                        }
                        
                        $count = $clientCount + $suspenseCount;
                        $message = "Reset balance for {$clientCount} clients and cleared {$suspenseCount} suspense accounts (Total: {$totalBalance})";
                        Log::info('Successfully reset client balances and cleared suspense accounts', ['client_count' => $clientCount, 'suspense_count' => $suspenseCount]);
                    } catch (\Exception $e) {
                        Log::error('Error resetting client balances and suspense accounts', ['error' => $e->getMessage()]);
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

                case 'client-balance-statements':
                    try {
                        Log::info('=== CLEARING CLIENT BALANCE STATEMENTS ===', [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'business_id' => $user->business_id,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent()
                        ]);

                        // Check if BalanceHistory model exists
                        if (!class_exists('App\Models\BalanceHistory')) {
                            Log::error('BalanceHistory model does not exist');
                            return response()->json([
                                'success' => false,
                                'message' => 'BalanceHistory model not found'
                            ], 500);
                        }

                        // Check if balance_histories table exists
                        if (!Schema::hasTable('balance_histories')) {
                            Log::error('balance_histories table does not exist');
                            return response()->json([
                                'success' => false,
                                'message' => 'balance_histories table not found'
                            ], 500);
                        }

                        // Count existing client balance history records
                        $existingCount = BalanceHistory::whereNotNull('client_id')->count();
                        Log::info('Existing client balance history records', ['count' => $existingCount]);

                        // Clear only client balance history records (where client_id is not null)
                        BalanceHistory::whereNotNull('client_id')->delete();
                        
                        Log::info('Client balance history records cleared successfully', [
                            'cleared_count' => $existingCount,
                            'user_id' => $user->id
                        ]);

                        $count = $existingCount;
                        $message = "Successfully cleared {$existingCount} client balance statement records";

                    } catch (\Exception $e) {
                        Log::error('Error clearing client balance history records', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'user_id' => $user->id
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'An error occurred while clearing client balance statements: ' . $e->getMessage()
                        ], 500);
                    }
                    break;

                case 'temp-accounts':
                    Log::info('SUCCESS: temp-accounts case reached!');
                    $message = "TEMP ACCOUNTS CASE WORKING!";
                    $count = 1;
                    break;

                case 'package-tracking':
                    try {
                        Log::info('=== CLEARING PACKAGE TRACKING DATA ===');
                        
                        // Clear package tracking related data
                        $packageTrackingCount = 0;
                        $message = "Cleared package tracking data: ";
                        
                        // Clear service delivery queues (package deliveries)
                        $queueCount = ServiceDeliveryQueue::count();
                        if ($queueCount > 0) {
                            ServiceDeliveryQueue::truncate();
                            $packageTrackingCount += $queueCount;
                            $message .= "{$queueCount} service delivery queues, ";
                            Log::info('Cleared service delivery queues', ['count' => $queueCount]);
                        }
                        
                        // Clear package suspense accounts
                        $packageSuspenseAccounts = MoneyAccount::where('type', 'package_suspense_account')->get();
                        $packageSuspenseCount = $packageSuspenseAccounts->count();
                        $packageSuspenseBalance = $packageSuspenseAccounts->sum('balance');
                        
                        if ($packageSuspenseCount > 0) {
                            MoneyAccount::where('type', 'package_suspense_account')
                                ->update(['balance' => 0]);
                            $packageTrackingCount += $packageSuspenseCount;
                            $message .= "{$packageSuspenseCount} package suspense accounts (Total: {$packageSuspenseBalance}), ";
                            Log::info('Cleared package suspense accounts', ['count' => $packageSuspenseCount, 'total_balance' => $packageSuspenseBalance]);
                        }
                        
                        // Clear package-related transactions
                        $packageTransactions = Transaction::where('transaction_for', 'package')
                            ->orWhere('description', 'like', '%package%')
                            ->orWhere('description', 'like', '%delivery%')
                            ->get();
                        $packageTransactionCount = $packageTransactions->count();
                        
                        if ($packageTransactionCount > 0) {
                            Transaction::where('transaction_for', 'package')
                                ->orWhere('description', 'like', '%package%')
                                ->orWhere('description', 'like', '%delivery%')
                                ->delete();
                            $packageTrackingCount += $packageTransactionCount;
                            $message .= "{$packageTransactionCount} package transactions, ";
                            Log::info('Cleared package transactions', ['count' => $packageTransactionCount]);
                        }
                        
                        // Remove trailing comma and space
                        $message = rtrim($message, ', ');
                        
                        if ($packageTrackingCount === 0) {
                            $message = "No package tracking data found to clear";
                        }
                        
                        $count = $packageTrackingCount;
                        Log::info('Successfully cleared package tracking data', ['total_count' => $packageTrackingCount]);
                        
                    } catch (\Exception $e) {
                        Log::error('Error clearing package tracking data', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                    break;
                    
                case 'temp_accounts':
                case 'test123':
                    try {
                        Log::info('=== CLEARING SUSPENSE ACCOUNTS ===');
                        
                        // Clear suspense account balances
                        $suspenseAccounts = MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])->get();
                        $count = $suspenseAccounts->count();
                        $totalBalance = $suspenseAccounts->sum('balance');
                        
                        Log::info('Found suspense accounts', ['count' => $count, 'total_balance' => $totalBalance]);
                        
                        // Reset balances to 0
                        MoneyAccount::whereIn('type', ['general_suspense_account', 'package_suspense_account'])
                            ->update(['balance' => 0]);
                        
                        $message = "Cleared {$count} suspense accounts (Total: {$totalBalance})";
                        Log::info('Successfully cleared suspense accounts');
                        
                    } catch (\Exception $e) {
                        Log::error('Error clearing suspense accounts', ['error' => $e->getMessage()]);
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
