<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PaymentLink;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class PaymentLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("payment-links.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    /**
     * Display the specified resource.
     */
    public function store(Request $request)
    {
        try {

            // 'card','mobile_money','bank_transfer','crypto'
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|string|max:50',
                'amount' => 'nullable|numeric',
                'minimum_amount' => 'nullable|numeric',
                'description' => 'nullable|string',
                'redirect_url' => 'nullable|url',
                'expiry_date' => 'nullable|date',
                'is_fixed' => 'nullable|boolean',
                'customer_fields' => 'nullable|array',
                'customer_fields.*' => 'in:name,email,phone_number',
                'method' => 'required|in:card,mobile_money,bank_transfer,crypto',
                'is_customer_info_required' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);
            // Ensure that at least one of amount or minimum_amount is provided
            if (!$request->is_fixed && !$request->minimum_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please provide a minimum amount for the payment link.');
            }

            //if method is not mobile money then the rest are coming soon
            if ($request->method !== 'mobile_money') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Currently, only mobile money payment links are supported. Other methods will be available soon.');
            }

            $reference = time() . '-' . auth()->id();

            PaymentLink::create([
                'title' => $request->title,
                'reference' => $reference,
                'type' => $request->type,
                'amount' => $request->is_fixed ? $request->amount : null,
                'minimum_amount' => !$request->is_fixed ? $request->minimum_amount : null,
                'description' => $request->description,
                'redirect_url' => $request->redirect_url,
                'expiry_date' => $request->expiry_date,
                'is_customer_info_required' => $request->has('is_customer_info_required'),
                'customer_fields' => $request->has('is_customer_info_required') ? json_encode($request->customer_fields) : null,
                'user_id' => auth()->id(),
                'business_id' => auth()->user()->business_id,
                'is_fixed' => $request->has('is_fixed') ? $request->is_fixed : false,
                'currency' => 'UGX',
                'is_active' => true,
                'date' => now(),
                'method' => $request->method,
            ]);

            return redirect()->back()->with('success', 'Payment link created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating payment link: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the payment link. Please try again.');
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }



    public function show(PaymentLink $paymentLink)
    {
        // Eager load the related business
        $paymentLink->load('business');

        // Check if the link is active
        if (!$paymentLink->is_active) {
            return view('payment-links.inactive');
        }

        // Check expiry
        if ($paymentLink->expiry_date && Carbon::parse($paymentLink->expiry_date)->isPast()) {
            return view('payment-links.expired');
        }

        return view('payment-links.show', compact('paymentLink'));
    }

    //pay
    public function pay(Request $request, PaymentLink $paymentLink)
    {
        try {
            // Validate common fields
            $rules = [
                'phone_number' => ['required', 'regex:/^[0-9]{9}$/'], // 9 digits only
            ];

            // Validate amount if not fixed
            if (!$paymentLink->is_fixed) {
                $rules['amount'] = ['required', 'numeric', 'min:' . $paymentLink->minimum_amount];
            }

            // Optional customer fields
            $fields = json_decode($paymentLink->customer_fields ?? '[]');
            if ($paymentLink->is_customer_info_required) {
                if (in_array('name', $fields)) {
                    $rules['name'] = 'required|string|max:255';
                }
                if (in_array('email', $fields)) {
                    $rules['email'] = 'required|email|max:255';
                }
            }

            $validated = $request->validate($rules);

            // Format phone number
            $phone = '256' . ltrim($validated['phone_number'], '0');
            $prefix = (int) substr($validated['phone_number'], 0, 3);

            // Determine provider (MTN or Airtel only)
            if ($prefix >= 700 && $prefix <= 759) {
                $provider = 'airtel';
            } elseif ($prefix >= 760 && $prefix <= 789) {
                $provider = 'mtn';
            } else {
                return redirect()->back()->withErrors(['phone_number' => 'Only Airtel and MTN numbers are allowed.']);
            }

            // Determine amount
            $amount = $paymentLink->is_fixed ? $paymentLink->amount : $validated['amount'];

            // Validate business
            $business = Business::find($paymentLink->business_id);
            if (!$business) {
                return redirect()->back()->withErrors(['error' => 'Business not found for this payment link.']);
            }

            // Calculate charge
            $chargePercentage = $business->percentage_charge ?? 0;
            $chargeAmount = round(($chargePercentage / 100) * $amount);

            // Generate unique reference
            $reference = '25' . now()->format('YmdHis') . rand(1000, 9999);

            // Main payment transaction
            Transaction::create([
                'business_id' => $business->id,
                'reference' => $reference,
                'transaction_for' => 'main',
                'amount' => $amount,
                'description' => $paymentLink->title,
                'status' => 'pending',
                'type' => 'credit',
                'origin' => 'payment_link',
                'phone_number' => $phone,
                'provider' => $provider,
                'service' => 'collection',
                'currency' => 'UGX',
                'method' => $paymentLink->method,
                'names' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Charge transaction
            if ($chargeAmount > 0) {
                Transaction::create([
                    'business_id' => $business->id,
                    'reference' => $reference,
                    'transaction_for' => 'charge',
                    'amount' => $chargeAmount,
                    'description' => 'Transaction charge for ' . $paymentLink->title,
                    'status' => 'pending',
                    'type' => 'debit',
                    'origin' => 'payment_link',
                    'phone_number' => $phone,
                    'provider' => $provider,
                    'service' => 'collection',
                    'currency' => 'UGX',
                    'method' => $paymentLink->method,
                    'names' => $validated['name'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // (Optional) Dispatch payment job here...

            return redirect()->back()->with('success', 'Payment initialized successfully. Reference: ' . $reference);
        } catch (\Throwable $e) {
            dd($e); // For debugging, remove in production
            Log::error('Payment Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Something went wrong while processing the payment. Please try again later.']);
        }
    }

    //pay

}
