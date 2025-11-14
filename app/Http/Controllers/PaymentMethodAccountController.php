<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethodAccount;
use App\Models\PaymentMethodAccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodAccountController extends Controller
{
    public function __construct()
    {
        // Only allow Kashtre users (business_id = 1) with proper permissions to access these settings
        $this->middleware(function ($request, $next) {
            if (auth()->user()->business_id !== 1) {
                abort(403, 'Access denied. This feature is only available to Kashtre administrators.');
            }
            return $next($request);
        });
    }

    public function transactions(PaymentMethodAccount $paymentMethodAccount)
    {
        if (!in_array('View Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to view payment method account transactions.');
        }

        // Load the account with relationships
        $paymentMethodAccount->load(['business', 'createdBy']);

        return view('settings.payment-method-accounts.transactions', compact('paymentMethodAccount'));
    }

    public function show(PaymentMethodAccount $paymentMethodAccount, \App\Models\PaymentMethodAccountTransaction $transaction)
    {
        if (!in_array('View Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to view payment method account transactions.');
        }

        // Ensure the transaction belongs to the account
        if ($transaction->payment_method_account_id !== $paymentMethodAccount->id) {
            abort(404, 'Transaction not found.');
        }

        // Ensure the account belongs to the current business
        if ($paymentMethodAccount->business_id !== auth()->user()->business_id) {
            abort(403, 'Access denied. You do not have permission to view this account.');
        }

        // Load relationships
        $transaction->load(['client', 'invoice', 'createdBy', 'business', 'paymentMethodAccount']);
        $paymentMethodAccount->load(['business']);

        return view('settings.payment-method-accounts.transaction-show', compact('paymentMethodAccount', 'transaction'));
    }
}
