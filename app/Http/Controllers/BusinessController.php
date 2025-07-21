<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\BusinessCreatedMail;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('businesses.index');
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $validated['logo'] = $logoPath;
            }

            // Generate time-based account number with prefix 'KS'
            $validated['account_number'] = 'KS' . time();

            //dd($validated); // For debugging purposes, remove in production

            try {
                // Create business
                $business = Business::create($validated);
                
                // Force refresh from database to get the latest data
                $business->refresh();
                
                // Send welcome email
                Mail::to($business->email)->send(new BusinessCreatedMail($business));
                
                $debugData = [
                    'business_data' => [
                        'id' => $business->id,
                        'email' => $business->email,
                        'account_number' => $business->account_number
                    ]
                ];
            } catch (\Exception $e) {
                \Log::error('Business creation error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'An error occurred while creating the business.');
            }

            return redirect()->back()->with('success', 'Business created successfully! Debug: ' . json_encode($debugData));

        // } catch (\Illuminate\Database\QueryException $e) {
            // if ($e->getCode() == 23000) { // Unique constraint violation
            //     return redirect()->back()->with('error', 'Account number already exists. Please try again.');
            // }

            // Log::error('DB error while creating business: ' . $e->getMessage());
            // return redirect()->back()->with('error', 'A database error occurred. Please contact support.');
        } catch (\Exception $e) {
            Log::error('General error while creating business: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Handle logo upload if provided
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                    Storage::disk('public')->delete($business->logo);
                }
                $logoPath = $request->file('logo')->store('logos', 'public');
                $validated['logo'] = $logoPath;
            }

            $business->update($validated);

            return redirect()->back()->with('success', 'Business updated successfully!');
        } catch (\Exception $e) {
            Log::error('General error while updating business: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        //
    }
}
