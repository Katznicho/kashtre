# Vendor Transaction Sync Implementation

## Overview
This implementation enables automatic synchronization of payment transactions from Kashtre healthcare billing system to the third-party vendor (insurance company) system at `http://localhost:8001`.

## What Was Implemented

### 1. Kashtre Side - Transaction Sender
**Service:** `App\Services\VendorTransactionSyncService`
- **Location:** `/app/Services/VendorTransactionSyncService.php`
- **Purpose:** Sends completed transactions to vendor system
- **Key Method:** `syncTransactionToVendor($transaction)`
  - Extracts insurance company authorizations from invoice
  - Sends transaction data to each insurance company's vendor system
  - Handles multiple insurance authorizations per invoice

**Controller Integration:** `App\Http\Controllers\LocalPaymentController`
- Updated to use `VendorTransactionSyncService`

**Command Integration:** `App\Console\Commands\SimulateSuccessfulPayments`
- Updated to sync transactions when payment is marked as completed
- Integrates vendor sync after `InsuranceClientPortionThirdPartyNotifier`

**Configuration:** `config/services.php` & `.env`
- Added `services.vendor.api_url` config pointing to vendor system
- `.env`: `VENDOR_API_URL=http://localhost:8001`

### 2. Vendor Side - Transaction Receiver
**API Controller:** `App\Http\Controllers\Api\TransactionSyncController`
- **Location:** `/app/Http/Controllers/Api/TransactionSyncController.php`
- **Route:** `POST /api/v1/transactions/record-from-kashtre`
- **Middleware:** Validates connection status before processing

**Service:** `App\Services\VendorTransactionRecordingService`
- **Location:** `/app/Services/VendorTransactionRecordingService.php`
- **Purpose:** Records incoming Kashtre transactions locally
- **Creates:**
  1. `Transaction` record with:
     - `transaction_number`: Prefixed with "KST-"
     - `transaction_status`: "cleared"
     - `metadata`: Includes Kashtre transaction ID, invoice details, auth reference, insurance portion
  2. `Payment` record with:
     - `payment_type`: "insurance_contribution"
     - `status`: "cleared"
     - `payment_reference`: Unique reference with Kashtre details
     - `payment_metadata`: Full sync details

**API Route:** `routes/api.php`
- Added import: `use App\Http\Controllers\Api\TransactionSyncController;`
- Added route in protected group with connection validation

## Payment Sync Data Flow

```
Kashtre Payment Simulator
    ↓
1. User creates invoice with insurance authorization
   - Invoice stored with insurance_authorization_snapshot
   - Contains: insurance_company_id, amount, auth_reference
    ↓
2. Payment collected (pending transaction created)
    ↓
3. php artisan payments:simulate-success --limit=1
    ↓
4. Transaction marked as "completed"
    ↓
5. VendorTransactionSyncService called
    ↓
6. For each insurance company in invoice:
   - Create payload with transaction details
   - POST to http://localhost:8001/api/v1/transactions/record-from-kashtre
    ↓
Vendor System
    ↓
7. TransactionSyncController receives request
    ↓
8. VendorTransactionRecordingService creates records
    ↓
9. Transaction + Payment records created locally
    ↓
10. Vendor dashboards can now query and display transactions
```

## Payload Example

When Kashtre sends a transaction to vendor:

```json
{
  "transaction_id": 54,
  "external_reference": "LOCAL-1776933660-6739",
  "invoice_id": 96,
  "invoice_number": "SYNC-20260423114100",
  "client_id": 58,
  "client_name": "DOE PAUL Test",
  "amount": 200,
  "status": "completed",
  "payment_status": "Paid",
  "insurance_company_id": 6,
  "authorization_reference": "AUTH-SYNC-b0e22339",
  "insurance_portion": 3000,
  "transaction_date": "2026-04-23T11:40:00Z",
  "description": "Test vendor sync - client portion payment",
  "business_id": 4
}
```

## Vendor Recording Result

The vendor system creates two records:

**Transaction Record:**
```
ID: (auto)
transaction_number: KST-54
reference_number: SYNC-20260423114100
amount: 200.00
debit_amount: 200.00
transaction_status: cleared
metadata: {
  kashtre_transaction_id: 54,
  kashtre_invoice_number: SYNC-20260423114100,
  kashtre_client_name: DOE PAUL Test,
  authorization_reference: AUTH-SYNC-b0e22339,
  insurance_portion: 3000,
  sync_timestamp: 2026-04-23T11:40:46Z,
  sync_type: kashtre_auto_sync
}
```

**Payment Record:**
```
ID: (auto)
payment_reference: KST-LOCAL-1776933660-6739
payment_type: insurance_contribution
amount: 200.00
paid_amount: 200.00
status: cleared
transaction_id: (linked to above)
payment_metadata: {
  kashtre_transaction_id: 54,
  kashtre_invoice_number: SYNC-20260423114100,
  kashtre_client_name: DOE PAUL Test,
  authorization_reference: AUTH-SYNC-b0e22339,
  insurance_portion: 3000
}
```

## Features

✅ **Multiple Insurance Support**
- Sends transaction to each insurance company involved in payment
- Each receives their own transaction record

✅ **Automatic Tracking**
- Insurance portion amounts preserved in metadata
- Authorization references linked for reconciliation
- Kashtre-vendor traceability maintained

✅ **Error Handling**
- Logs all sync attempts
- Handles missing authorizations gracefully
- Vendor sync failures don't block payment completion

✅ **Connection Validation**
- Vendor endpoint protected with connection status middleware
- Ensures Kashtre system is properly configured in vendor

## Files Created

1. `/Users/katendenicholas/Desktop/laravel/kashtre/app/Services/VendorTransactionSyncService.php`
2. `/Users/katendenicholas/Desktop/laravel/third-party/app/Services/VendorTransactionRecordingService.php`
3. `/Users/katendenicholas/Desktop/laravel/third-party/app/Http/Controllers/Api/TransactionSyncController.php`

## Files Modified

1. `/Users/katendenicholas/Desktop/laravel/kashtre/app/Http/Controllers/LocalPaymentController.php`
   - Added import for VendorTransactionSyncService

2. `/Users/katendenicholas/Desktop/laravel/kashtre/app/Console/Commands/SimulateSuccessfulPayments.php`
   - Added import for VendorTransactionSyncService
   - Added sync call after transaction completion
   - Fixed JSON string handling for invoice items

3. `/Users/katendenicholas/Desktop/laravel/kashtre/config/services.php`
   - Added vendor service configuration

4. `/Users/katendenicholas/Desktop/laravel/kashtre/.env`
   - Added `VENDOR_API_URL=http://localhost:8001`

5. `/Users/katendenicholas/Desktop/laravel/third-party/routes/api.php`
   - Added import for TransactionSyncController
   - Added route for receiving transactions

## How to Verify

### Step 1: Check Kashtre has pending transaction
```bash
cd /Users/katendenicholas/Desktop/laravel/kashtre
php artisan tinker
> DB::table('transactions')->where('status', 'pending')->count()
```

### Step 2: Run payment simulation (triggers vendor sync)
```bash
php artisan payments:simulate-success --limit=1
```

### Step 3: Check vendor system received transaction
```bash
cd /Users/katendenicholas/Desktop/laravel/third-party
php artisan tinker
> DB::table('transactions')->where('transaction_number', 'like', 'KST-%')->get()
> DB::table('payments')->where('payment_reference', 'like', 'KST-%')->get()
```

### Step 4: Visit vendor payments page
- Navigate to `http://localhost:8001/payments`
- Look for new transaction entries

## Logs

Check logs to verify sync completion:

**Kashtre logs:**
```
storage/logs/laravel.log
- "Syncing completed transaction to vendor system"
- "Vendor sync completed"
```

**Vendor logs:**
```
storage/logs/laravel.log
- "TransactionSyncController: Received transaction sync request from Kashtre"
- "VendorTransactionRecordingService: Receiving transaction from Kashtre"
- "VendorTransactionRecordingService: Transaction recorded successfully"
```

## Next Steps

1. Test full payment flow with vendor website display
2. Monitor logs for sync success/failures
3. Add retry logic for failed syncs (optional)
4. Implement vendor-to-Kashtre feedback (acknowledge receipt)
5. Add UI notification when transactions appear on vendor pages
