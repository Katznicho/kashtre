# Quick Start: Vendor Transaction Sync

## What This Does

When you process a payment in Kashtre:
1. ✅ Payment is marked as completed
2. ✅ Transaction is automatically sent to vendor system (localhost:8001)
3. ✅ Vendor receives and records it in their database
4. ✅ Vendors can see transactions on their dashboards

## Test the System

### Option 1: Test with Existing Invoice (Fastest)
```bash
# Go to Kashtre
cd /Users/katendenicholas/Desktop/laravel/kashtre

# Simulate a payment
php artisan payments:simulate-success --limit=1
```

### Option 2: Create Fresh Test Invoice & Transaction
```bash
cd /Users/katendenicholas/Desktop/laravel/kashtre

php artisan tinker << 'EOF'
// Create invoice
$invoice = \App\Models\Invoice::create([
  'business_id' => 4,
  'branch_id' => 6,
  'client_id' => 58,
  'visit_id' => 'TEST-' . uniqid(),
  'client_name' => 'Test Client',
  'invoice_number' => 'TEST-' . date('YmdHis'),
  'status' => 'confirmed',
  'payment_status' => 'pending',
  'currency' => 'USD',
  'items' => json_encode([['id' => 3, 'name' => 'Test Item', 'price' => 5000]]),
  'subtotal' => 5000,
  'service_charge' => 100,
  'total_amount' => 5100,
  'amount_paid' => 0,
  'balance_due' => 5100,
  'payment_methods' => json_encode(['insurance','mobile_money']),
  'insurance_client_total' => 200,
  'insurance_insurance_total' => 4900,
  'insurance_authorization_snapshot' => json_encode([
    ['insurance_company_id' => 6, 'insurance_company_name' => 'AAR Insurance', 'authorization_reference' => 'AUTH-' . uniqid(), 'amount' => 3000],
    ['insurance_company_id' => 11, 'insurance_company_name' => 'Earth One', 'authorization_reference' => 'AUTH-' . uniqid(), 'amount' => 1900]
  ])
]);

// Create transaction
$transaction = \App\Models\Transaction::create([
  'business_id' => 4,
  'branch_id' => 6,
  'client_id' => 58,
  'invoice_id' => $invoice->id,
  'amount' => 200,
  'reference' => $invoice->invoice_number,
  'external_reference' => 'LOCAL-' . time() . '-' . rand(1000,9999),
  'status' => 'pending',
  'method' => 'mobile_money',
  'provider' => 'yo',
  'date' => now(),
  'currency' => 'USD',
  'phone_number' => '+256759983853',
  'names' => 'Test Client'
]);

echo "✓ Invoice: " . $invoice->invoice_number . "\n";
echo "✓ Transaction: " . $transaction->id . "\n";
EOF
```

### Then Simulate
```bash
php artisan payments:simulate-success --limit=1
```

## Verify Vendor Received Transaction

### Check Vendor Database
```bash
cd /Users/katendenicholas/Desktop/laravel/third-party

php artisan tinker << 'EOF'
// Check transactions
echo "Vendor Transactions: " . DB::table('transactions')->where('transaction_number', 'like', 'KST-%')->count() . "\n";

// Check payments
echo "Vendor Payments: " . DB::table('payments')->where('payment_reference', 'like', 'KST-%')->count() . "\n";

// Get latest
$txn = DB::table('transactions')->where('transaction_number', 'like', 'KST-%')->latest()->first();
if ($txn) {
  echo "\nLatest Transaction:\n";
  echo "  ID: " . $txn->id . "\n";
  echo "  Number: " . $txn->transaction_number . "\n";
  echo "  Amount: " . $txn->amount . "\n";
  echo "  Status: " . $txn->transaction_status . "\n";
}
EOF
```

### Visit Vendor Pages
- Payments: http://localhost:8001/payments
- AAR Insurance: http://localhost:8001/third-party-vendors/6
- Earth One: http://localhost:8001/third-party-vendors/11

## What Gets Synced

For each transaction sent to vendor:

**Transaction Record Created:**
- transaction_number: "KST-{kashtre_id}"
- amount: (from payment)
- status: "cleared"
- metadata: {kashtre_id, invoice_number, client_name, auth_reference, insurance_portion}

**Payment Record Created:**
- payment_reference: "KST-{external_reference}"
- amount: (from payment)
- status: "cleared"
- payment_metadata: {kashtre details}

## Troubleshooting

### Check if vendor received request
Look in vendor logs:
```bash
cd /Users/katendenicholas/Desktop/laravel/third-party
tail -50 storage/logs/laravel.log | grep "TransactionSyncController"
```

### Check if Kashtre sent request
Look in Kashtre logs:
```bash
cd /Users/katendenicholas/Desktop/laravel/kashtre
tail -50 storage/logs/laravel.log | grep -i "Vendor\|vendor\|sync"
```

### Verify Kashtre config
```bash
php artisan tinker
> config('services.vendor.api_url')
# Should return: http://localhost:8001
```

### Check env variable
```bash
grep VENDOR_API_URL .env
# Should show: VENDOR_API_URL=http://localhost:8001
```

## Files Involved

**Kashtre:**
- `/app/Services/VendorTransactionSyncService.php` - Sends transactions
- `/app/Console/Commands/SimulateSuccessfulPayments.php` - Triggers sync
- `/config/services.php` - Config
- `/.env` - Vendor URL

**Vendor:**
- `/app/Services/VendorTransactionRecordingService.php` - Receives & records
- `/app/Http/Controllers/Api/TransactionSyncController.php` - API endpoint
- `/routes/api.php` - Route definition

## Key Points

✅ Transactions sync automatically after payment simulation
✅ Multiple insurance companies per invoice = multiple transaction records
✅ All Kashtre transaction details preserved in vendor records
✅ Insurance portion amounts tracked for each company
✅ Authorization references linked for reconciliation
✅ Sync failures don't block payment (logged but non-blocking)
