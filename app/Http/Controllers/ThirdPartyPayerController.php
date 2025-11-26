<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyPayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThirdPartyPayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user has permission
        if (!in_array('View Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view third party payers.');
        }

        return view('third-party-payers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user has permission
        if (!in_array('Add Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('third-party-payers.index')->with('error', 'You do not have permission to add third party payers.');
        }

        // This is handled by Livewire component, but we can add a view if needed
        return redirect()->route('third-party-payers.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission
        if (!in_array('Add Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('third-party-payers.index')->with('error', 'You do not have permission to add third party payers.');
        }

        // This is handled by Livewire component
        return redirect()->route('third-party-payers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ThirdPartyPayer $thirdPartyPayer)
    {
        // Check if user has permission
        if (!in_array('View Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view third party payers.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $thirdPartyPayer->business_id !== Auth::user()->business_id) {
            return redirect()->route('third-party-payers.index')->with('error', 'Access denied.');
        }

        $thirdPartyPayer->load(['business', 'insuranceCompany', 'client']);

        return view('third-party-payers.show', compact('thirdPartyPayer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThirdPartyPayer $thirdPartyPayer)
    {
        // Check if user has permission
        if (!in_array('Edit Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('third-party-payers.index')->with('error', 'You do not have permission to edit third party payers.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $thirdPartyPayer->business_id !== Auth::user()->business_id) {
            return redirect()->route('third-party-payers.index')->with('error', 'Access denied.');
        }

        // This is handled by Livewire component
        return redirect()->route('third-party-payers.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdPartyPayer $thirdPartyPayer)
    {
        // Check if user has permission
        if (!in_array('Edit Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('third-party-payers.index')->with('error', 'You do not have permission to edit third party payers.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $thirdPartyPayer->business_id !== Auth::user()->business_id) {
            return redirect()->route('third-party-payers.index')->with('error', 'Access denied.');
        }

        // This is handled by Livewire component
        return redirect()->route('third-party-payers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThirdPartyPayer $thirdPartyPayer)
    {
        // Check if user has permission
        if (!in_array('Delete Third Party Payers', Auth::user()->permissions ?? [])) {
            return redirect()->route('third-party-payers.index')->with('error', 'You do not have permission to delete third party payers.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $thirdPartyPayer->business_id !== Auth::user()->business_id) {
            return redirect()->route('third-party-payers.index')->with('error', 'Access denied.');
        }

        // This is handled by Livewire component
        return redirect()->route('third-party-payers.index');
    }
}
