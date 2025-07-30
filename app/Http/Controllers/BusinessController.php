<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\NewBusinessCreatedMail;

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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $validated['logo'] = $logoPath;
            } else {
                $validated['logo'] = null;
            }

            // Generate time-based account number with prefix '25' and random 2-digit suffix
            $validated['account_number'] = 'KS' . time();

            //dd($validated); // For debugging purposes, remove in production

            // Create business
            $business = Business::create($validated);

            // dd($business->email);

            // Send welcome email
            // Mail::to($business->email)->send(new BusinessCreatedMail($business));
            Mail::to($business->email)->send(new NewBusinessCreatedMail($business));


            Log::info('BusinessCreatedMail details:', [
                'name' => $business->name,
                'phone' => $business->phone,
                'email' => $business->email,
                'account_number' => $business->account_number,
            ]);

            return redirect()->back()->with('success', 'Business created successfully!');

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
        // Check authorization - admin can view all, users can only view their own business
        if (auth()->user()->business_id !== 1 && auth()->user()->business_id !== $business->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        // Only allow editing for admin users or if it's their own business
        if (auth()->user()->business_id !== 1 && auth()->user()->business_id !== $business->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('businesses.edit_business', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        // Only allow updating for admin users or if it's their own business
        if (auth()->user()->business_id !== 1 && auth()->user()->business_id !== $business->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                    Storage::disk('public')->delete($business->logo);
                }
                
                $logoPath = $request->file('logo')->store('logos', 'public');
                $validated['logo'] = $logoPath;
            }

            // Update business
            $business->update($validated);

            Log::info('Business updated successfully:', [
                'business_id' => $business->id,
                'name' => $business->name,
                'updated_by' => auth()->user()->id,
            ]);

            return redirect()->route('businesses.index')->with('success', 'Business updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error while updating business: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.')->withInput();
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
