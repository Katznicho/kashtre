<?php

namespace App\Http\Controllers;

use App\Models\MaturationPeriod;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaturationPeriodController extends Controller
{
    public function __construct()
    {
        // Only allow Kashtre users (business_id = 1) to access these settings
        $this->middleware(function ($request, $next) {
            if (auth()->user()->business_id !== 1) {
                abort(403, 'Access denied. This feature is only available to Kashtre administrators.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $maturationPeriods = MaturationPeriod::with(['business', 'createdBy', 'updatedBy'])
            ->orderBy('business_id')
            ->orderBy('payment_method')
            ->get();

        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        $paymentMethods = ['mtn', 'airtel', 'yo', 'cash', 'bank_transfer'];

        return view('settings.maturation-periods.index', compact('maturationPeriods', 'businesses', 'paymentMethods'));
    }

    public function create()
    {
        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        $paymentMethods = ['mtn', 'airtel', 'yo', 'cash', 'bank_transfer'];

        return view('settings.maturation-periods.create', compact('businesses', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'payment_method' => 'required|in:mtn,airtel,yo,cash,bank_transfer',
            'maturation_days' => 'required|integer|min:0|max:365',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Check if a maturation period already exists for this business and payment method
        $existing = MaturationPeriod::where('business_id', $validated['business_id'])
            ->where('payment_method', $validated['payment_method'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A maturation period already exists for this business and payment method combination.');
        }

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        MaturationPeriod::create($validated);

        return redirect()->route('maturation-periods.index')
            ->with('success', 'Maturation period created successfully.');
    }

    public function edit(MaturationPeriod $maturationPeriod)
    {
        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        $paymentMethods = ['mtn', 'airtel', 'yo', 'cash', 'bank_transfer'];

        return view('settings.maturation-periods.edit', compact('maturationPeriod', 'businesses', 'paymentMethods'));
    }

    public function update(Request $request, MaturationPeriod $maturationPeriod)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'payment_method' => 'required|in:mtn,airtel,yo,cash,bank_transfer',
            'maturation_days' => 'required|integer|min:0|max:365',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Check if another maturation period exists for this business and payment method combination
        $existing = MaturationPeriod::where('business_id', $validated['business_id'])
            ->where('payment_method', $validated['payment_method'])
            ->where('id', '!=', $maturationPeriod->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A maturation period already exists for this business and payment method combination.');
        }

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $maturationPeriod->update($validated);

        return redirect()->route('maturation-periods.index')
            ->with('success', 'Maturation period updated successfully.');
    }

    public function destroy(MaturationPeriod $maturationPeriod)
    {
        $maturationPeriod->delete();

        return redirect()->route('maturation-periods.index')
            ->with('success', 'Maturation period deleted successfully.');
    }

    public function toggleStatus(MaturationPeriod $maturationPeriod)
    {
        $maturationPeriod->update([
            'is_active' => !$maturationPeriod->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $maturationPeriod->is_active ? 'activated' : 'deactivated';

        return redirect()->route('maturation-periods.index')
            ->with('success', "Maturation period {$status} successfully.");
    }
}