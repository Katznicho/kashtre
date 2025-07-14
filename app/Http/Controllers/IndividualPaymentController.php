<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;


class IndividualPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("individual-payments.index");
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
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'phone_number' => ['required', 'regex:/^256[0-9]{9}$/'],
                'amount' => 'required|numeric|min:100',
                'method' => 'required|in:mobile_money,card,bank_transfer,crypto',
            ]);

            // Determine provider
            $prefix = (int) substr($validated['phone_number'], 3, 3);
            if ($prefix >= 700 && $prefix <= 759) {
                $provider = 'airtel';
            } elseif ($prefix >= 760 && $prefix <= 789) {
                $provider = 'mtn';
            } else {
                return back()->withErrors(['phone_number' => 'Only MTN and Airtel numbers are supported.']);
            }

            $business = auth()->user()->business ?? Business::first(); // or however you handle business ownership

            if (!$business) {
                return back()->withErrors(['error' => 'Business not found.']);
            }

            $chargePercentage = $business->percentage_charge ?? 0;
            $chargeAmount = round(($chargePercentage / 100) * $validated['amount']);

            $reference = '25' . now()->format('YmdHis') . rand(1000, 9999);

            // Main payout transaction
            Transaction::create([
                'business_id' => $business->id,
                'reference' => $reference,
                'transaction_for' => 'individual_payout',
                'amount' => $validated['amount'],
                'description' => 'Individual payment to ' . $validated['recipient_name'],
                'status' => 'pending',
                'type' => 'debit', // you're sending money out
                'origin' => 'web',
                'phone_number' => $validated['phone_number'],
                'provider' => $provider,
                'service' => 'disbursement',
                'currency' => 'UGX',
                'method' => $validated['method'],
                'names' => $validated['recipient_name'],
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
                    'description' => 'Charge for individual payout to ' . $validated['recipient_name'],
                    'status' => 'pending',
                    'type' => 'debit',
                    'origin' => 'web',
                    'phone_number' => $validated['phone_number'],
                    'provider' => $provider,
                    'service' => 'disbursement',
                    'currency' => 'UGX',
                    'method' => $validated['method'],
                    'names' => $validated['recipient_name'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Optional: Dispatch payout job here
            return back()->with('success', 'Payment initialized successfully. Reference: ' . $reference);
        } catch (\Throwable $e) {
            dd($e); // remove in production

            Log::error('Individual Payment Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
