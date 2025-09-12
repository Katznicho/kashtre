@component('mail::message')
# Payment Receipt

Dear {{ $client->name }},

Thank you for your payment! We are pleased to confirm that your transaction has been successfully processed.

## Transaction Details

**Invoice Number:** {{ $invoice->invoice_number }}  
**Payment Date:** {{ $invoice->created_at->format('F d, Y \a\t g:i A') }}  
**Payment Method:** {{ ucfirst(implode(', ', $invoice->payment_methods ?? ['Cash'])) }}  
**Amount Paid:** UGX {{ number_format($invoice->amount_paid, 2) }}

## Business Information
**Business:** {{ $business->name }}  
**Phone:** {{ $business->phone }}  
**Email:** {{ $business->email }}

## Items Purchased

@component('mail::table')
| Item | Quantity | Price Each | Total |
|------|----------|------------|-------|
@foreach($invoice->items as $item)
| {{ $item['name'] ?? 'N/A' }} | {{ $item['quantity'] ?? 1 }} | UGX {{ number_format($item['price'] ?? 0, 2) }} | UGX {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }} |
@endforeach
@endcomponent

## Payment Summary
**Subtotal 1:** UGX {{ number_format($invoice->subtotal, 2) }}  
@if($invoice->package_adjustment > 0)
**Package Adjustment:** -UGX {{ number_format($invoice->package_adjustment, 2) }}  
@endif
@if($invoice->account_balance_adjustment > 0)
**Account Balance Adjustment:** -UGX {{ number_format($invoice->account_balance_adjustment, 2) }}  
@endif
**Subtotal 2:** UGX {{ number_format($invoice->subtotal - ($invoice->package_adjustment ?? 0) - ($invoice->account_balance_adjustment ?? 0), 2) }}  
@if($invoice->service_charge > 0)
**Service Charge:** UGX {{ number_format($invoice->service_charge, 2) }}  
@endif
**Total:** UGX {{ number_format($invoice->total_amount, 2) }}  
**Amount Paid:** UGX {{ number_format($invoice->amount_paid, 2) }}  
**Balance Due:** UGX {{ number_format($invoice->balance_due, 2) }}

@if($invoice->notes)
## Additional Notes
{{ $invoice->notes }}
@endif

---

**Payment Status:** {{ ucfirst($invoice->payment_status) }}  
**Invoice Status:** {{ ucfirst($invoice->status) }}

A detailed receipt is attached to this email for your records.

Thank you for choosing {{ $business->name }}!

Best regards,  
{{ $business->name }} Team

---

*This is an automated receipt. Please keep this email for your records.*
@endcomponent
