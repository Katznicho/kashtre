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
}
