<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalSetting;
use App\Models\Business;
use App\Models\User;
use App\Models\ContractorProfile;
use App\Models\WithdrawalSettingApprover;
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
        if (!in_array('View Withdrawal Settings', Auth::user()->permissions ?? [])) {
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
        if (!in_array('Add Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to add withdrawal settings.');
        }

        $businesses = Business::all();
        $withdrawalTypes = Constants::WITHDRAWAL_TYPES;
        
        // Get users and contractors for approver selection
        $users = User::where('status', 'active')->get();
        $contractors = ContractorProfile::with('user')->get();

        return view('withdrawal-settings.create', compact('businesses', 'withdrawalTypes', 'users', 'contractors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add withdrawal settings
        if (!in_array('Add Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to add withdrawal settings.');
        }

        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'minimum_withdrawal_amount' => 'required|numeric|min:0',
            'number_of_free_withdrawals_per_day' => 'required|integer|min:0',
            'min_business_approvers' => 'required|integer|min:1',
            'min_kashtre_approvers' => 'required|integer|min:1',
            'withdrawal_type' => 'required|in:regular,express',
            'is_active' => 'boolean',
            'business_approvers' => 'required|array|min:1',
            'business_approvers.*' => 'required|string',
            'kashtre_approvers' => 'required|array|min:1',
            'kashtre_approvers.*' => 'required|string'
        ]);

        $withdrawalSetting = WithdrawalSetting::create([
            'business_id' => $request->business_id,
            'minimum_withdrawal_amount' => $request->minimum_withdrawal_amount,
            'number_of_free_withdrawals_per_day' => $request->number_of_free_withdrawals_per_day,
            'min_business_approvers' => $request->min_business_approvers,
            'min_kashtre_approvers' => $request->min_kashtre_approvers,
            'withdrawal_type' => $request->withdrawal_type,
            'is_active' => $request->has('is_active')
        ]);

        // Handle approver selection
        $this->syncApprovers($withdrawalSetting, $request);

        return redirect()->route('withdrawal-settings.index')
            ->with('success', 'Withdrawal setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalSetting $withdrawalSetting)
    {
        // Check if user has permission to view withdrawal settings
        if (!in_array('View Withdrawal Settings', Auth::user()->permissions ?? [])) {
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
        if (!in_array('Edit Withdrawal Settings', Auth::user()->permissions ?? [])) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'You do not have permission to edit withdrawal settings.');
        }

        // Check if the withdrawal setting belongs to the user's business
        if ($withdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businesses = Business::all();
        $withdrawalTypes = Constants::WITHDRAWAL_TYPES;
        
        // Get users and contractors for approver selection
        $users = User::where('status', 'active')->get();
        $contractors = ContractorProfile::with('user')->get();

        return view('withdrawal-settings.edit', compact('withdrawalSetting', 'businesses', 'withdrawalTypes', 'users', 'contractors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalSetting $withdrawalSetting)
    {
        // Check if user has permission to edit withdrawal settings
        if (!in_array('Edit Withdrawal Settings', Auth::user()->permissions ?? [])) {
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
            'is_active' => 'boolean',
            'business_approvers' => 'required|array|min:1',
            'business_approvers.*' => 'required|string',
            'kashtre_approvers' => 'required|array|min:1',
            'kashtre_approvers.*' => 'required|string'
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

        // Handle approver selection
        $this->syncApprovers($withdrawalSetting, $request);

        return redirect()->route('withdrawal-settings.index')
            ->with('success', 'Withdrawal setting updated successfully.');
    }

    /**
     * Sync approvers for a withdrawal setting
     */
    private function syncApprovers(WithdrawalSetting $withdrawalSetting, Request $request)
    {
        // Clear existing approvers
        $withdrawalSetting->approvers()->delete();

        // Add business approvers
        if ($request->has('business_approvers')) {
            foreach ($request->business_approvers as $approverData) {
                if ($approverData) {
                    $this->parseAndAddApprover($withdrawalSetting, $approverData, 'business');
                }
            }
        }

        // Add kashtre approvers
        if ($request->has('kashtre_approvers')) {
            foreach ($request->kashtre_approvers as $approverData) {
                if ($approverData) {
                    $this->parseAndAddApprover($withdrawalSetting, $approverData, 'kashtre');
                }
            }
        }
    }

    /**
     * Parse approver data and add to withdrawal setting
     */
    private function parseAndAddApprover(WithdrawalSetting $withdrawalSetting, string $approverData, string $level)
    {
        // Format: "type:id" (e.g., "user:123" or "contractor:456")
        $parts = explode(':', $approverData);
        if (count($parts) === 2) {
            [$type, $id] = $parts;
            
            WithdrawalSettingApprover::create([
                'withdrawal_setting_id' => $withdrawalSetting->id,
                'approver_id' => $id,
                'approver_type' => $type,
                'approver_level' => $level
            ]);
        }
    }

}
