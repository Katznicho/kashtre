@component('mail::message')
# Transaction Record - KashTre Platform

Dear KashTre Team,

A transaction has been completed on the platform. Below are the transaction details for your records.

## Transaction Summary

**Invoice Number:** {{ $invoice->invoice_number }}  
**Transaction Date:** {{ $invoice->created_at->format('F d, Y \a\t g:i A') }}  
**Payment Method:** {{ ucfirst(implode(', ', $invoice->payment_methods ?? ['Cash'])) }}  
**Total Transaction Amount:** UGX {{ number_format($invoice->total_amount, 2) }}  
**KashTre Service Charge:** UGX {{ number_format($chargeAmount, 2) }}  
**Amount Paid:** UGX {{ number_format($invoice->amount_paid, 2) }}

## Client Information
**Client Name:** {{ $client->name ?? 'N/A' }}  
**Client ID:** {{ $client->client_id ?? 'N/A' }}

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
