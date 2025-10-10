<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessWithdrawalSetting;
use App\Traits\AccessTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessWithdrawalSettingController extends Controller
{
    use AccessTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user has permission
        if (!in_array('View Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view business withdrawal settings.');
        }

        // If user is from Kashtre (business_id = 1), show all settings
        // Otherwise, show only their business settings
        if (Auth::user()->business_id == 1) {
            $settings = BusinessWithdrawalSetting::with('business', 'creator')->latest()->paginate(15);
        } else {
            $settings = BusinessWithdrawalSetting::with('business', 'creator')
                ->where('business_id', Auth::user()->business_id)
                ->latest()
                ->paginate(15);
        }

        return view('business-withdrawal-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user has permission
        if (!in_array('Add Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'You do not have permission to add business withdrawal settings.');
        }

        $businesses = Business::all();

        return view('business-withdrawal-settings.create', compact('businesses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission
        if (!in_array('Add Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'You do not have permission to add business withdrawal settings.');
        }

        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'withdrawal_charges' => 'required|array|min:1',
            'withdrawal_charges.*.lower_bound' => 'required|numeric|min:0',
            'withdrawal_charges.*.upper_bound' => 'required|numeric|min:0',
            'withdrawal_charges.*.charge_amount' => 'required|numeric|min:0',
            'withdrawal_charges.*.charge_type' => 'required|in:fixed,percentage',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->withdrawal_charges as $charge) {
                BusinessWithdrawalSetting::create([
                    'business_id' => $request->business_id,
                    'lower_bound' => $charge['lower_bound'],
                    'upper_bound' => $charge['upper_bound'],
                    'charge_amount' => $charge['charge_amount'],
                    'charge_type' => $charge['charge_type'],
                    'description' => $charge['description'] ?? null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('business-withdrawal-settings.index')
                ->with('success', 'Business withdrawal settings created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create settings: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessWithdrawalSetting $businessWithdrawalSetting)
    {
        // Check if user has permission
        if (!in_array('View Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view business withdrawal settings.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $businessWithdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businessWithdrawalSetting->load('business', 'creator');

        return view('business-withdrawal-settings.show', compact('businessWithdrawalSetting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessWithdrawalSetting $businessWithdrawalSetting)
    {
        // Check if user has permission
        if (!in_array('Edit Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'You do not have permission to edit business withdrawal settings.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $businessWithdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businesses = Business::all();

        return view('business-withdrawal-settings.edit', compact('businessWithdrawalSetting', 'businesses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessWithdrawalSetting $businessWithdrawalSetting)
    {
        // Check if user has permission
        if (!in_array('Edit Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'You do not have permission to edit business withdrawal settings.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $businessWithdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'lower_bound' => 'required|numeric|min:0',
            'upper_bound' => 'required|numeric|min:0',
            'charge_amount' => 'required|numeric|min:0',
            'charge_type' => 'required|in:fixed,percentage',
        ]);

        $businessWithdrawalSetting->update([
            'business_id' => $request->business_id,
            'lower_bound' => $request->lower_bound,
            'upper_bound' => $request->upper_bound,
            'charge_amount' => $request->charge_amount,
            'charge_type' => $request->charge_type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('business-withdrawal-settings.index')
            ->with('success', 'Business withdrawal setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessWithdrawalSetting $businessWithdrawalSetting)
    {
        // Check if user has permission
        if (!in_array('Delete Business Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'You do not have permission to delete business withdrawal settings.');
        }

        // Check access
        if (Auth::user()->business_id != 1 && $businessWithdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('business-withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businessWithdrawalSetting->delete();

        return redirect()->route('business-withdrawal-settings.index')
            ->with('success', 'Business withdrawal setting deleted successfully.');
    }
}
