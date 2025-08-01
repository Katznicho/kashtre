<?php

namespace App\Http\Controllers;

use App\Models\ContractorProfile;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $contractorProfiles = ContractorProfile::with(['business', 'user'])->get();
        return view('contractor-profiles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $businesses = Business::all();
        $users = User::all();
        return view('contractor-profiles.create', compact('businesses', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'user_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_balance' => 'required|numeric|min:0',
            'kashtre_account_number' => 'nullable|string|max:255',
            'signing_qualifications' => 'nullable|string|max:255',
        ]);

        ContractorProfile::create($validated);

        return redirect()->route('contractor-profiles.index')
            ->with('success', 'Contractor profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractorProfile $contractorProfile)
    {
        $contractorProfile->load(['business', 'user']);
        return view('contractor-profiles.show', compact('contractorProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContractorProfile $contractorProfile)
    {
        $businesses = Business::all();
        $users = User::all();
        return view('contractor-profiles.edit', compact('contractorProfile', 'businesses', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractorProfile $contractorProfile)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'user_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_balance' => 'required|numeric|min:0',
            'kashtre_account_number' => 'nullable|string|max:255',
            'signing_qualifications' => 'nullable|string|max:255',
        ]);

        $contractorProfile->update($validated);

        return redirect()->route('contractor-profiles.index')
            ->with('success', 'Contractor profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractorProfile $contractorProfile)
    {
        $contractorProfile->delete();

        return redirect()->route('contractor-profiles.index')
            ->with('success', 'Contractor profile deleted successfully.');
    }
}
