# Local Payment Testing Guide

This guide explains how to test the complete payment flow locally when real payments don't work.

## 🎯 Overview

Since local development doesn't support real mobile money payments, we've created simulation commands that treat all pending payments as successful, allowing you to test the entire money movement and suspense account system.

## 🚀 Quick Start

### Option 1: Use the Test Script (Recommended)
```bash
./test-payment-flow.sh
```

### Option 2: Use Commands Directly
```bash
# Test complete flow (reset + simulate)
php artisan payments:test-flow --reset --limit=10

# Just simulate successful payments
php artisan payments:simulate-success --limit=10

# Reset transactions to pending
php artisan payments:reset-for-testing --confirm
```

## 📋 Available Commands

### 1. `payments:test-flow`
**Purpose**: Complete end-to-end test of the payment flow
```bash
php artisan payments:test-flow --reset --limit=10
```
**Options**:
- `--reset`: Reset completed transactions to pending before testing
- `--limit=N`: Maximum number of transactions to process

### 2. `payments:simulate-success`
**Purpose**: Simulate all pending payments as successful
```bash
php artisan payments:simulate-success --limit=10
```
**Options**:
- `--limit=N`: Maximum number of transactions to process

### 3. `payments:reset-for-testing`
**Purpose**: Reset completed transactions back to pending for testing
```bash
php artisan payments:reset-for-testing --confirm
```
**Options**:
- `--confirm`: Required flag to confirm the reset operation

## 🔄 Complete Testing Workflow

### Step 1: Create Test Data
1. Create invoices with items through the normal UI
2. Initiate mobile money payments (they will be pending)
3. Don't complete the actual payment

### Step 2: Run Simulation
```bash
# Option A: Use the test script
./test-payment-flow.sh

# Option B: Use commands directly
php artisan payments:test-flow --reset --limit=10
```

### Step 3: Verify Results
1. **Check Transaction Status**: All transactions should be marked as completed
2. **Check Invoice Status**: All invoices should be marked as paid
3. **Check Suspense Accounts**: Money should be moved to appropriate suspense accounts
4. **Check Balance Statements**: Contractor and business balance statements should be created

## 📊 What Gets Tested

### ✅ Transaction Processing
- Updates transaction status from `pending` to `completed`
- Updates invoice status from `pending` to `paid`
- Processes payment received through MoneyTrackingService

### ✅ Money Movement
- Moves money to suspense accounts (package, general, kashtre)
- Creates balance statements for contractors and businesses
- Tracks all money movements with proper logging

### ✅ Service Point Integration
- Queues items at service points
- Creates package tracking records
- Handles all post-payment processing

### ✅ Suspense Account Management
- Package Suspense: Funds for paid package items
- General Suspense: Funds for ordered items not yet offered
- Kashtre Suspense: Service fees and deposits
- Client Suspense: Individual client funds

## 🔍 Monitoring and Debugging

### View Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Search for specific events
grep "SIMULATING PAYMENT SUCCESS" storage/logs/laravel.log
grep "Suspense Account" storage/logs/laravel.log
```

### Check Database Status
```bash
# Check transaction statuses
php artisan tinker
>>> App\Models\Transaction::select('status', DB::raw('count(*) as count'))->groupBy('status')->get()

# Check suspense account balances
>>> App\Models\MoneyAccount::whereIn('type', ['package_suspense_account', 'general_suspense_account', 'kashtre_suspense_account'])->get(['type', 'balance', 'name'])
```

### View Suspense Accounts in Browser
Visit: `http://localhost:8000/suspense-accounts`

## 🎯 Testing Scenarios

### Scenario 1: Fresh Test
```bash
# Reset everything and test from scratch
php artisan payments:test-flow --reset --limit=5
```

### Scenario 2: Incremental Test
```bash
# Test only new pending transactions
php artisan payments:simulate-success --limit=3
```

### Scenario 3: Large Batch Test
```bash
# Test many transactions at once
php artisan payments:test-flow --reset --limit=50
```

## 🚨 Important Notes

### ⚠️ Safety Features
- All simulation commands include comprehensive logging
- Database transactions are used to ensure data integrity
- Error handling prevents partial updates
- Reset commands require explicit confirmation

### 🔒 Data Safety
- Simulation commands only affect `pending` transactions
- Reset commands only affect `completed` transactions
- All operations are logged for audit purposes

### 📈 Performance
- Commands process transactions in batches
- Database transactions ensure consistency
- Comprehensive logging for debugging

## 🐛 Troubleshooting

### Common Issues

1. **No Pending Transactions**
   - Create test invoices through the UI
   - Initiate mobile money payments
   - Don't complete the actual payment

2. **Simulation Not Working**
   - Check logs: `tail -f storage/logs/laravel.log`
   - Verify transactions exist: `php artisan tinker`
   - Check database connections

3. **Suspense Accounts Not Showing**
   - Verify MoneyTrackingService is working
   - Check if invoices have items
   - Verify client and business relationships

### Debug Commands
```bash
# Check transaction counts
php artisan tinker
>>> App\Models\Transaction::select('status', DB::raw('count(*) as count'))->groupBy('status')->get()

# Check suspense account balances
>>> App\Models\MoneyAccount::whereIn('type', ['package_suspense_account', 'general_suspense_account', 'kashtre_suspense_account'])->sum('balance')

# Check recent logs
tail -n 50 storage/logs/laravel.log
```

## 📝 Expected Results

After running the simulation, you should see:

1. **Transactions**: All pending transactions marked as completed
2. **Invoices**: All pending invoices marked as paid
3. **Suspense Accounts**: Money moved to appropriate suspense accounts
4. **Balance Statements**: Contractor and business balance statements created
5. **Service Points**: Items queued at service points
6. **Package Tracking**: Package tracking records created

## 🎉 Success Indicators

- ✅ All pending transactions are now completed
- ✅ All pending invoices are now paid
- ✅ Suspense accounts show appropriate balances
- ✅ Money movements are logged and tracked
- ✅ No errors in the logs
- ✅ Suspense accounts dashboard shows individual records

This testing approach allows you to verify the entire payment flow without needing real mobile money integration!
