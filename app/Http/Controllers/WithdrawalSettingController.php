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

        // If user is from Kashtre (business_id = 1), show all withdrawal settings
        // Otherwise, show only their business settings
        if (Auth::user()->business_id == 1) {
            $withdrawalSettings = WithdrawalSetting::with('business')->paginate(10);
        } else {
            $withdrawalSettings = WithdrawalSetting::with('business')
                ->where('business_id', Auth::user()->business_id)
                ->paginate(10);
        }

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
        
        // Get all users with their business info for dynamic filtering
        // For business approvers, we only want business users (not contractors)
        $users = User::where('status', 'active')
            ->whereDoesntHave('contractorProfile') // Exclude contractors from business approver selection
            ->select('id', 'name', 'email', 'business_id')
            ->get();

        return view('withdrawal-settings.create', compact('businesses', 'withdrawalTypes', 'users'));
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
            'withdrawal_type' => 'required|in:regular,express',
            'is_active' => 'boolean',
            
            // 3-Level Business Approval Configuration
            'min_business_initiators' => 'required|integer|min:1|max:2',
            'max_business_initiators' => 'required|integer|min:1|max:2',
            'min_business_authorizers' => 'required|integer|min:1|max:2',
            'max_business_authorizers' => 'required|integer|min:1|max:2',
            'min_business_approvers' => 'required|integer|min:1|max:2',
            'max_business_approvers' => 'required|integer|min:1|max:2',
            
            // 3-Level Kashtre Approval Configuration
            'min_kashtre_initiators' => 'required|integer|min:1|max:2',
            'max_kashtre_initiators' => 'required|integer|min:1|max:2',
            'min_kashtre_authorizers' => 'required|integer|min:1|max:2',
            'max_kashtre_authorizers' => 'required|integer|min:1|max:2',
            'min_kashtre_approvers' => 'required|integer|min:1|max:2',
            'max_kashtre_approvers' => 'required|integer|min:1|max:2',
            
            // Business Approvers (3 levels)
            'business_initiators' => 'required|array|min:1',
            'business_initiators.*' => 'required|string',
            'business_authorizers' => 'required|array|min:1',
            'business_authorizers.*' => 'required|string',
            'business_approvers' => 'required|array|min:1',
            'business_approvers.*' => 'required|string',
            
            // Kashtre Approvers (3 levels)
            'kashtre_initiators' => 'required|array|min:1',
            'kashtre_initiators.*' => 'required|string',
            'kashtre_authorizers' => 'required|array|min:1',
            'kashtre_authorizers.*' => 'required|string',
            'kashtre_approvers' => 'required|array|min:1',
            'kashtre_approvers.*' => 'required|string',
        ]);

        // Validate that we have the correct number of approvers for each level
        $this->validateApproverCounts($request);

        // Check if a withdrawal setting already exists for this business and withdrawal type
        $existingSetting = WithdrawalSetting::where('business_id', $request->business_id)
            ->where('withdrawal_type', $request->withdrawal_type)
            ->first();
        
        if ($existingSetting) {
            // Update existing setting
            $existingSetting->update([
                'minimum_withdrawal_amount' => $request->minimum_withdrawal_amount,
                'number_of_free_withdrawals_per_day' => $request->number_of_free_withdrawals_per_day,
                'withdrawal_type' => $request->withdrawal_type,
                'is_active' => $request->has('is_active'),
                
                // 3-Level Business Approval Configuration
                'min_business_initiators' => $request->min_business_initiators,
                'max_business_initiators' => $request->max_business_initiators,
                'min_business_authorizers' => $request->min_business_authorizers,
                'max_business_authorizers' => $request->max_business_authorizers,
                'min_business_approvers' => $request->min_business_approvers,
                'max_business_approvers' => $request->max_business_approvers,
                
                // 3-Level Kashtre Approval Configuration
                'min_kashtre_initiators' => $request->min_kashtre_initiators,
                'max_kashtre_initiators' => $request->max_kashtre_initiators,
                'min_kashtre_authorizers' => $request->min_kashtre_authorizers,
                'max_kashtre_authorizers' => $request->max_kashtre_authorizers,
                'min_kashtre_approvers' => $request->min_kashtre_approvers,
                'max_kashtre_approvers' => $request->max_kashtre_approvers,
            ]);

            // Handle approver selection
            $this->syncApprovers($existingSetting, $request);

            return redirect()->route('withdrawal-settings.index')
                ->with('success', 'Withdrawal setting updated successfully.');
        } else {
            // Create new setting
            $withdrawalSetting = WithdrawalSetting::create([
                'business_id' => $request->business_id,
                'minimum_withdrawal_amount' => $request->minimum_withdrawal_amount,
                'number_of_free_withdrawals_per_day' => $request->number_of_free_withdrawals_per_day,
                'withdrawal_type' => $request->withdrawal_type,
                'is_active' => $request->has('is_active'),
                
                // 3-Level Business Approval Configuration
                'min_business_initiators' => $request->min_business_initiators,
                'max_business_initiators' => $request->max_business_initiators,
                'min_business_authorizers' => $request->min_business_authorizers,
                'max_business_authorizers' => $request->max_business_authorizers,
                'min_business_approvers' => $request->min_business_approvers,
                'max_business_approvers' => $request->max_business_approvers,
                
                // 3-Level Kashtre Approval Configuration
                'min_kashtre_initiators' => $request->min_kashtre_initiators,
                'max_kashtre_initiators' => $request->max_kashtre_initiators,
                'min_kashtre_authorizers' => $request->min_kashtre_authorizers,
                'max_kashtre_authorizers' => $request->max_kashtre_authorizers,
                'min_kashtre_approvers' => $request->min_kashtre_approvers,
                'max_kashtre_approvers' => $request->max_kashtre_approvers,
            ]);

            // Handle approver selection
            $this->syncApprovers($withdrawalSetting, $request);

            return redirect()->route('withdrawal-settings.index')
                ->with('success', 'Withdrawal setting created successfully.');
        }
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

        // Check if the withdrawal setting belongs to the user's business (unless user is from Kashtre)
        if (Auth::user()->business_id != 1 && $withdrawalSetting->business_id !== Auth::user()->business_id) {
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

        // Check if the withdrawal setting belongs to the user's business (unless user is from Kashtre)
        if (Auth::user()->business_id != 1 && $withdrawalSetting->business_id !== Auth::user()->business_id) {
            return redirect()->route('withdrawal-settings.index')->with('error', 'Access denied.');
        }

        $businesses = Business::all();
        $withdrawalTypes = Constants::WITHDRAWAL_TYPES;
        
        // Get all users with their business info for dynamic filtering
        // For business approvers, we only want business users (not contractors)
        $users = User::where('status', 'active')
            ->whereDoesntHave('contractorProfile') // Exclude contractors from business approver selection
            ->select('id', 'name', 'email', 'business_id')
            ->get();

        return view('withdrawal-settings.edit', compact('withdrawalSetting', 'businesses', 'withdrawalTypes', 'users'));
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

        // Check if the withdrawal setting belongs to the user's business (unless user is from Kashtre)
        if (Auth::user()->business_id != 1 && $withdrawalSetting->business_id !== Auth::user()->business_id) {
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
            'business_approvers' => 'required|array',
            'business_approvers.*' => 'required|string',
            'kashtre_approvers' => 'required|array',
            'kashtre_approvers.*' => 'required|string'
        ]);

        // Validate that we have at least the minimum number of approvers
        if (count($request->business_approvers) < $request->min_business_approvers) {
            return back()->withErrors([
                'business_approvers' => "You must select at least {$request->min_business_approvers} business approvers."
            ])->withInput();
        }

        if (count($request->kashtre_approvers) < $request->min_kashtre_approvers) {
            return back()->withErrors([
                'kashtre_approvers' => "You must select at least {$request->min_kashtre_approvers} Kashtre approvers."
            ])->withInput();
        }

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
     * Sync approvers for a withdrawal setting (3-level system)
     */
    private function syncApprovers(WithdrawalSetting $withdrawalSetting, Request $request)
    {
        // Clear existing approvers
        $withdrawalSetting->approvers()->delete();

        // Business Approvers - 3 levels
        $businessLevels = [
            'initiator' => 'business_initiators',
            'authorizer' => 'business_authorizers',
            'approver' => 'business_approvers'
        ];

        foreach ($businessLevels as $level => $requestKey) {
            if ($request->has($requestKey)) {
                foreach ($request->$requestKey as $approverData) {
                    if ($approverData) {
                        $this->parseAndAddApprover($withdrawalSetting, $approverData, 'business', $level);
                    }
                }
            }
        }

        // Kashtre Approvers - 3 levels
        $kashtreLevels = [
            'initiator' => 'kashtre_initiators',
            'authorizer' => 'kashtre_authorizers',
            'approver' => 'kashtre_approvers'
        ];

        foreach ($kashtreLevels as $level => $requestKey) {
            if ($request->has($requestKey)) {
                foreach ($request->$requestKey as $approverData) {
                    if ($approverData) {
                        $this->parseAndAddApprover($withdrawalSetting, $approverData, 'kashtre', $level);
                    }
                }
            }
        }
    }

    /**
     * Parse approver data and add to withdrawal setting (3-level system)
     */
    private function parseAndAddApprover(WithdrawalSetting $withdrawalSetting, string $approverData, string $approverLevel, string $approvalLevel)
    {
        // Format: "type:id" (e.g., "user:123" or "contractor:456")
        $parts = explode(':', $approverData);
        if (count($parts) === 2) {
            [$type, $id] = $parts;
            
            WithdrawalSettingApprover::create([
                'withdrawal_setting_id' => $withdrawalSetting->id,
                'approver_id' => $id,
                'approver_type' => $type,
                'approver_level' => $approverLevel,
                'approval_level' => $approvalLevel
            ]);
        }
    }

    /**
     * Validate approver counts for 3-level system
     */
    private function validateApproverCounts(Request $request)
    {
        $errors = [];

        // Validate Business Approvers
        $businessLevels = [
            'initiator' => ['min' => $request->min_business_initiators, 'max' => $request->max_business_initiators, 'selected' => count($request->business_initiators ?? [])],
            'authorizer' => ['min' => $request->min_business_authorizers, 'max' => $request->max_business_authorizers, 'selected' => count($request->business_authorizers ?? [])],
            'approver' => ['min' => $request->min_business_approvers, 'max' => $request->max_business_approvers, 'selected' => count($request->business_approvers ?? [])]
        ];

        foreach ($businessLevels as $level => $config) {
            if ($config['selected'] < $config['min']) {
                $errors["business_{$level}"] = "You must select at least {$config['min']} business {$level}.";
            }
            if ($config['selected'] > $config['max']) {
                $errors["business_{$level}"] = "You cannot select more than {$config['max']} business {$level}.";
            }
        }

        // Validate Kashtre Approvers
        $kashtreLevels = [
            'initiator' => ['min' => $request->min_kashtre_initiators, 'max' => $request->max_kashtre_initiators, 'selected' => count($request->kashtre_initiators ?? [])],
            'authorizer' => ['min' => $request->min_kashtre_authorizers, 'max' => $request->max_kashtre_authorizers, 'selected' => count($request->kashtre_authorizers ?? [])],
            'approver' => ['min' => $request->min_kashtre_approvers, 'max' => $request->max_kashtre_approvers, 'selected' => count($request->kashtre_approvers ?? [])]
        ];

        foreach ($kashtreLevels as $level => $config) {
            if ($config['selected'] < $config['min']) {
                $errors["kashtre_{$level}"] = "You must select at least {$config['min']} Kashtre {$level}.";
            }
            if ($config['selected'] > $config['max']) {
                $errors["kashtre_{$level}"] = "You cannot select more than {$config['max']} Kashtre {$level}.";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }
    }

}
