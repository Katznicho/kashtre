@component('mail::message')
# Transaction Record - KashTre Platform

Dear KashTre Team,

A transaction has been completed on the platform. Below are the transaction details for your records.

## Commission Summary

**Invoice Number:** {{ $invoice->invoice_number }}  
**Transaction Date:** {{ $invoice->created_at->format('F d, Y \a\t g:i A') }}  
**Commission Earned:** UGX {{ number_format($chargeAmount, 2) }}  
**Status:** {{ ucfirst($invoice->status) }}

A detailed transaction record is attached to this email for your platform records.

Best regards,  
Kashtre System

---

*This is an automated platform notification for KashTre internal records.*
@endcomponent
