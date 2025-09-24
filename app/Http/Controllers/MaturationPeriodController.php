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
        // Only allow Kashtre users (business_id = 1) with proper permissions to access these settings
        $this->middleware(function ($request, $next) {
            if (auth()->user()->business_id !== 1) {
                abort(403, 'Access denied. This feature is only available to Kashtre administrators.');
            }
            
            // Check for View Maturation Periods permission
            if (!in_array('View Maturation Periods', auth()->user()->permissions ?? [])) {
                abort(403, 'Access denied. You do not have permission to view maturation periods.');
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
        $paymentMethods = ['insurance', 'credit_arrangement', 'mobile_money', 'v_card', 'p_card', 'bank_transfer', 'cash'];

        return view('settings.maturation-periods.index', compact('maturationPeriods', 'businesses', 'paymentMethods'));
    }

    public function create()
    {
        // Check for Add Maturation Periods permission
        if (!in_array('Add Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to add maturation periods.');
        }

        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        $paymentMethods = ['insurance', 'credit_arrangement', 'mobile_money', 'v_card', 'p_card', 'bank_transfer', 'cash'];

        return view('settings.maturation-periods.create', compact('businesses', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        // Check for Add Maturation Periods permission
        if (!in_array('Add Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to add maturation periods.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'payment_method' => 'required|in:insurance,credit_arrangement,mobile_money,v_card,p_card,bank_transfer,cash',
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
        // Check for Edit Maturation Periods permission
        if (!in_array('Edit Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to edit maturation periods.');
        }

        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        $paymentMethods = ['insurance', 'credit_arrangement', 'mobile_money', 'v_card', 'p_card', 'bank_transfer', 'cash'];

        return view('settings.maturation-periods.edit', compact('maturationPeriod', 'businesses', 'paymentMethods'));
    }

    public function update(Request $request, MaturationPeriod $maturationPeriod)
    {
        // Check for Edit Maturation Periods permission
        if (!in_array('Edit Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to edit maturation periods.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'payment_method' => 'required|in:insurance,credit_arrangement,mobile_money,v_card,p_card,bank_transfer,cash',
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
        // Check for Delete Maturation Periods permission
        if (!in_array('Delete Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to delete maturation periods.');
        }

        $maturationPeriod->delete();

        return redirect()->route('maturation-periods.index')
            ->with('success', 'Maturation period deleted successfully.');
    }

    public function toggleStatus(MaturationPeriod $maturationPeriod)
    {
        // Check for Manage Maturation Periods permission
        if (!in_array('Manage Maturation Periods', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to manage maturation periods.');
        }

        $maturationPeriod->update([
            'is_active' => !$maturationPeriod->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $maturationPeriod->is_active ? 'activated' : 'deactivated';

        return redirect()->route('maturation-periods.index')
            ->with('success', "Maturation period {$status} successfully.");
    }
}