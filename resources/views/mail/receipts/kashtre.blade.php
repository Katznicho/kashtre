@component('mail::message')
# Transaction Record - KashTre Platform

Dear KashTre Team,

A transaction has been completed on the platform. Below are the transaction details for your records.

## Transaction Summary

**Invoice Number:** {{ $invoice->invoice_number }}  
**Transaction Date:** {{ $invoice->created_at->format('F d, Y \a\t g:i A') }}  
**Payment Method:** {{ ucfirst(implode(', ', $invoice->payment_methods ?? ['Cash'])) }}  
**Total Transaction Amount:** UGX {{ number_format($invoice->total_amount, 2) }}  
**KashTre Charge Amount:** UGX {{ number_format($chargeAmount, 2) }}

## Business Information
**Business Name:** {{ $business->name ?? 'N/A' }}  
**Business ID:** {{ $business->id ?? 'N/A' }}  
**Business Email:** {{ $business->email ?? 'N/A' }}  
**Business Phone:** {{ $business->phone ?? 'N/A' }}

## Client Information
**Client Name:** {{ $client->name }}  
**Client ID:** {{ $client->client_id }}  
**Client Phone:** {{ $client->phone_number }}

## Transaction Breakdown
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

## Items Involved
@foreach($invoice->items as $item)
- **{{ $item['name'] ?? 'N/A' }}** (Qty: {{ $item['quantity'] ?? 1 }}) - UGX {{ number_format($item['price'] ?? 0, 2) }}
@endforeach

---

**Payment Status:** {{ ucfirst($invoice->payment_status) }}  
**Invoice Status:** {{ ucfirst($invoice->status) }}  
**Service Charge:** UGX {{ number_format($chargeAmount, 2) }}

A detailed transaction record is attached to this email for your platform records.

Best regards,  
Kashtre System

---

*This is an automated platform notification for KashTre internal records.*
@endcomponent
