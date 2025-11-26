<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessSettingsController extends Controller
{
    /**
     * Show the form for editing business settings.
     */
    public function edit()
    {
        // Check if user has permission
        if (!in_array('View Business Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view business settings.');
        }

        $business = Business::findOrFail(Auth::user()->business_id);

        return view('business-settings.edit', compact('business'));
    }

    /**
     * Update business settings.
     */
    public function update(Request $request)
    {
        // Check if user has permission
        if (!in_array('Edit Business Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit business settings.');
        }

        $business = Business::findOrFail(Auth::user()->business_id);

        $validated = $request->validate([
            'max_third_party_credit_limit' => 'nullable|numeric|min:0',
            'max_first_party_credit_limit' => 'nullable|numeric|min:0',
            'admit_button_label' => 'nullable|string|max:255',
            'discharge_button_label' => 'nullable|string|max:255',
            'default_payment_terms_days' => 'nullable|integer|min:1|max:365',
        ]);

        $business->update([
            'max_third_party_credit_limit' => $validated['max_third_party_credit_limit'] ?? null,
            'max_first_party_credit_limit' => $validated['max_first_party_credit_limit'] ?? null,
            'admit_button_label' => $validated['admit_button_label'] ?? 'Admit Patient',
            'discharge_button_label' => $validated['discharge_button_label'] ?? 'Discharge Patient',
            'default_payment_terms_days' => $validated['default_payment_terms_days'] ?? 30,
        ]);

        return redirect()->route('business-settings.edit')
            ->with('success', 'Business settings updated successfully.');
    }
}
