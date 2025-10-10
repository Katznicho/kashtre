<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalSetting;
use App\Models\Business;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\AccessTrait;

class WithdrawalSettingController extends Controller
{
    use AccessTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user has permission to view withdrawal settings
        if (!in_array('View Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view withdrawal settings.');
        }

        $withdrawalSettings = WithdrawalSetting::with('business')
            ->where('business_id', Auth::user()->business_id)
            ->paginate(10);

        return view('withdrawal-settings.index', compact('withdrawalSettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user has permission to add withdrawal settings
        if (!in_array('Add Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to add withdrawal settings.');
        }

        $businesses = Business::all();
        $withdrawalTypes = Constants::WITHDRAWAL_TYPES;

        return view('withdrawal-settings.create', compact('businesses', 'withdrawalTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add withdrawal settings
        if (!in_array('Add Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to add withdrawal settings.');
        }

        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'minimum_withdrawal_amount' => 'required|numeric|min:0',
            'number_of_free_withdrawals_per_day' => 'required|integer|min:0',
            'min_business_approvers' => 'required|integer|min:1',
            'min_kashtre_approvers' => 'required|integer|min:1',
            'withdrawal_type' => 'required|in:regular,express',
            'is_active' => 'boolean'
        ]);

        WithdrawalSetting::create([
            'business_id' => $request->business_id,
            'minimum_withdrawal_amount' => $request->minimum_withdrawal_amount,
            'number_of_free_withdrawals_per_day' => $request->number_of_free_withdrawals_per_day,
            'min_business_approvers' => $request->min_business_approvers,
            'min_kashtre_approvers' => $request->min_kashtre_approvers,
            'withdrawal_type' => $request->withdrawal_type,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('withdrawal-settings.index')
            ->with('success', 'Withdrawal setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalSetting $withdrawalSetting)
    {
        // Check if user has permission to view withdrawal settings
        if (!in_array('View Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view withdrawal settings.');
        }

        // Check if the withdrawal setting belongs to the user's business
        if ($withdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $withdrawalSetting->load('business');
        return view('withdrawal-settings.show', compact('withdrawalSetting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WithdrawalSetting $withdrawalSetting)
    {
        // Check if user has permission to edit withdrawal settings
        if (!in_array('Edit Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to edit withdrawal settings.');
        }

        // Check if the withdrawal setting belongs to the user's business
        if ($withdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businesses = Business::all();
        $withdrawalTypes = Constants::WITHDRAWAL_TYPES;

        return view('withdrawal-settings.edit', compact('withdrawalSetting', 'businesses', 'withdrawalTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalSetting $withdrawalSetting)
    {
        // Check if user has permission to edit withdrawal settings
        if (!in_array('Edit Withdrawal Settings', json_decode(Auth::user()->permissions ?? '[]'))) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to edit withdrawal settings.');
        }

        // Check if the withdrawal setting belongs to the user's business
        if ($withdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'minimum_withdrawal_amount' => 'required|numeric|min:0',
            'number_of_free_withdrawals_per_day' => 'required|integer|min:0',
            'min_business_approvers' => 'required|integer|min:1',
            'min_kashtre_approvers' => 'required|integer|min:1',
            'withdrawal_type' => 'required|in:regular,express',
            'is_active' => 'boolean'
        ]);

        $withdrawalSetting->update([
            'business_id' => $request->business_id,
            'minimum_withdrawal_amount' => $request->minimum_withdrawal_amount,
            'number_of_free_withdrawals_per_day' => $request->number_of_free_withdrawals_per_day,
            'min_business_approvers' => $request->min_business_approvers,
            'min_kashtre_approvers' => $request->min_kashtre_approvers,
            'withdrawal_type' => $request->withdrawal_type,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('withdrawal-settings.index')
            ->with('success', 'Withdrawal setting updated successfully.');
    }

}
