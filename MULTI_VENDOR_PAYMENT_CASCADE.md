# Multi-Vendor Payment Cascade System

## Overview

The multi-vendor payment cascade system allows a single client to be registered with multiple insurance companies (vendors). When an invoice is created, the system automatically processes insurance authorization through each vendor in priority order, cascading the remaining balance from one vendor to the next.

## How It Works

### 1. Client Registration with Multiple Vendors

When creating a client:
- Select **multiple insurance companies** (e.g., AAR Insurance, Earth One)
- **Verify each vendor's policy** independently
- System automatically assigns **priority order** (non-OE vendors first, then OE vendors)
- Each vendor is attached with their own **policy number** and **verification status**

### 2. Authorized Visits

- Each vendor receives a **separate authorized visit record** in their portal
- Visits are registered automatically during client creation
- Vendors can see client details specific to their policy

### 3. Invoice Authorization Cascade

When an invoice is created and submitted for authorization:

```
Total Invoice: UGX 1,000

↓ VENDOR 1 (Priority 1 - AAR Insurance)
  └─ Submits: UGX 1,000 (full amount)
  └─ AAR approves: Insurance covers UGX 400
  └─ Client owes: UGX 600 (passed to next vendor)

↓ VENDOR 2 (Priority 2 - Earth One)
  └─ Submits: UGX 600 (remaining balance)
  └─ Earth One approves: Insurance covers UGX 300
  └─ Client owes: UGX 300 (passed to next vendor)

↓ VENDOR 3+ (if any)
  └─ Continues cascade...

↓ FINAL CLIENT PORTION
  └─ Client pays: UGX 300 (what NO vendor covers)
```

## Real-World Example

### Scenario
Patient **JOHN KASULE** is registered with:
- **AAR Insurance** (Policy: 978-20260419-RSK63N)
- **Earth One Regular** (Policy: ZON-20260419-S1LB76)

### Invoice Created
- **Total amount**: UGX 760
- **Items**: Various medical services

### Authorization Flow

#### Step 1: AAR Insurance (Priority 1)
```
Input Amount: UGX 760
Policy: 978-20260419-RSK63N

AAR Response:
├─ Authorization: Approved ✓
├─ Insurance Covers: UGX 400
├─ Deductible Applied: UGX 0
├─ Copay Applied: UGX 0
├─ Remaining for Client: UGX 360
└─ Passed to Earth One: UGX 360
```

#### Step 2: Earth One (Priority 2)
```
Input Amount: UGX 360 (from AAR)
Policy: ZON-20260419-S1LB76

Earth One Response:
├─ Authorization: Approved ✓
├─ Insurance Covers: UGX 300
├─ Deductible Applied: UGX 0
├─ Copay Applied: UGX 0
├─ Remaining for Client: UGX 60
└─ Final Client Portion: UGX 60
```

### Final Invoice Breakdown

| Component | Amount |
|-----------|--------|
| **Total Invoice** | UGX 760 |
| **AAR Insurance Paid** | UGX 400 |
| **Earth One Insurance Paid** | UGX 300 |
| **Total Insurance Coverage** | UGX 700 |
| **Final Client Portion** | **UGX 60** |

## System Components

### 1. Database Models

**ClientVendor**
```php
- client_id: Foreign key to Client
- third_party_payer_id: Foreign key to ThirdPartyPayer
- policy_number: Vendor's policy number
- priority: Processing order (1, 2, 3...)
- policy_verified: Boolean
- physical_insurance_card_verified: Boolean
- status: active/suspended/blocked
```

### 2. Key Methods

**MultiVendorClientService::attachMultipleVendors()**
- Attaches multiple vendors to a client
- Creates ClientVendor records with priority assignment
- Syncs clients to each vendor system

**InvoiceController::runMultiVendorCascade()**
- Executes the cascade authorization flow
- Processes vendors in priority order
- Accumulates insurance and client totals
- Stores complete breakdown in invoice snapshot

### 3. Invoice Storage

**insurance_authorization_snapshot** (JSON):
```json
{
  "multi_vendor": true,
  "vendors": [
    {
      "vendor_name": "AAR Insurance",
      "priority": 1,
      "amount_submitted": 760,
      "insurance_total": 400,
      "client_total": 360,
      "authorization_status": "approved"
    },
    {
      "vendor_name": "Earth One",
      "priority": 2,
      "amount_submitted": 360,
      "insurance_total": 300,
      "client_total": 60,
      "authorization_status": "approved"
    }
  ],
  "insurance_total": 700,
  "client_total": 60,
  "authorization_status": "approved"
}
```

## Priority Assignment

### Non-Open Enrollment (Regular) Vendors
- Get **lower priority numbers** (1, 2, 3...)
- Processed **first**

### Open Enrollment Vendors
- Get **higher priority numbers** (processed after OE)
- Processed **last**

### Example Priority Order:
1. AAR Insurance (Regular) → Priority 1
2. Earth One (Regular) → Priority 2
3. Some OE Vendor → Priority 3

## Viewing Multi-Vendor Breakdown

### On Invoice Detail Page

Navigate to **Invoice Details** → Scroll to **Insurance Authorization** section:

```
Insurance Authorization — Approved

Vendor Breakdown (Cascade Order):

1. AAR Insurance
   Priority: 1
   Amount Submitted: UGX 760
   Insurance Paid: UGX 400 ✓
   Client Portion: UGX 360
   Status: Approved

2. Earth One
   Priority: 2
   Amount Submitted: UGX 360
   Insurance Paid: UGX 300 ✓
   Client Portion: UGX 60
   Status: Approved

Total Insurance Coverage: UGX 700
Final Client Portion: UGX 60
```

### On Client POS Page

When viewing a multi-vendor client at `/pos/item-selection/{client}`:

```
Insurance Companies

┌─ AAR Insurance
│  Policy: 978-20260419-RSK63N
│  ✓ Verified | Card Verified
│
└─ Earth One Regular
   Policy: ZON-20260419-S1LB76
   ✓ Verified | Card Verified
```

## Key Features

### ✅ Automatic Priority Assignment
- Non-OE vendors processed first
- Maintains submission order within each group

### ✅ Cascade Processing
- Each vendor receives the remaining balance
- Prevents double-billing

### ✅ Full Audit Trail
- All vendor responses stored
- Complete breakdown visible
- Easy reconciliation

### ✅ Selective Vendor Handling
- Suspended/blocked vendors skipped
- Partial failure doesn't stop flow
- Warnings logged

### ✅ Per-Vendor Settings
- Each vendor has own policy number
- Each vendor has own verification status
- Each vendor processes independently

## Edge Cases

### Suspended Vendor
If a vendor is suspended:
```
Authorization Status: Skipped
Error: "Vendor is suspended"
Amount: 0 (skipped to next vendor)
```

### Vendor Rejects Authorization
```
Authorization Status: Auto-Rejected
Error: "Policy number not found"
Amount: 0
Client portion: Full amount owed by client
```

### Multiple Rejections
If all vendors reject:
```
Final Authorization Status: Auto-Rejected
Insurance Total: UGX 0
Client Portion: Full invoice amount
```

## Testing Multi-Vendor Flow

### Step 1: Create Client
1. Go to **Client Registration**
2. Enter client details
3. **Select 2+ insurance companies**
4. **Verify each policy**
5. Click **Register Visit**

### Step 2: Create Invoice
1. Go to **Item Selection** for the client
2. Add items/services
3. Click **Create Invoice**

### Step 3: Request Authorization
1. Go to **Invoice Details**
2. Click **Request Insurance Authorization**
3. System automatically cascades through vendors

### Step 4: View Breakdown
1. Scroll to **Insurance Authorization** section
2. See vendor-by-vendor breakdown
3. View final client portion

## API Integration

The system communicates with each vendor's API:

```
POST /api/v1/invoices/authorize

Payload:
{
  "invoice_number": "P2026040001",
  "total_amount": 760,
  "insurance_company_id": "AAR_ID",
  "policy_number": "978-20260419-RSK63N",
  "items": [...],
  "services_category": "outpatient"
}

Response:
{
  "success": true,
  "insurance_total": 400,
  "client_total": 360,
  "authorization_reference": "AUTH_123456"
}
```

## Troubleshooting

### No Insurance Companies Showing
- Ensure client has **active vendors** attached
- Verify vendors are not **suspended/blocked**
- Check that **policies are verified**

### Cascade Not Processing
- Verify **at least 1 vendor** is active
- Check vendor **priority order** is correct
- Ensure vendor has valid **policy number**

### Client Portion Incorrect
- Verify all vendor responses were received
- Check for deductible/copay impacts
- Review authorization snapshot

## Related Files

- **Model**: `app/Models/ClientVendor.php`
- **Service**: `app/Services/MultiVendorClientService.php`
- **Controller**: `app/Http/Controllers/InvoiceController.php`
- **View**: `resources/views/invoices/show.blade.php`
- **Migration**: `database/migrations/*_create_client_vendors_table.php`

## Summary

The multi-vendor cascade system provides:
- ✅ Multiple vendors per client
- ✅ Automatic priority-based processing
- ✅ Cascading balance calculation
- ✅ Complete audit trail
- ✅ Transparent client breakdown
- ✅ Vendor-independent authorization

This ensures accurate insurance coverage calculation and prevents billing disputes when clients are covered by multiple insurance companies.
