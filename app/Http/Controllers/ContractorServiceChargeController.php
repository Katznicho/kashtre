<?php

namespace App\Http\Controllers;

use App\Models\ContractorServiceCharge;
use App\Models\ContractorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContractorServiceChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        return view('contractor-service-charges.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available contractors for the current business
        // Super admins (business_id = 1) can see all contractors
        if ($businessId === 1) {
            $contractors = ContractorProfile::with(['user', 'business'])->get();
        } else {
            $contractors = ContractorProfile::where('business_id', $businessId)
                ->with(['user', 'business'])
                ->get();
        }

        return view('contractor-service-charges.create', compact('contractors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $validator = Validator::make($request->all(), [
            'contractor_profile_id' => 'required|integer|exists:contractor_profiles,id',
            'service_charges' => 'required|array|min:1',
            'service_charges.*.amount' => 'required|numeric|min:0',
            'service_charges.*.upper_bound' => 'nullable|numeric|min:0',
            'service_charges.*.lower_bound' => 'nullable|numeric|min:0',
            'service_charges.*.type' => 'required|in:fixed,percentage',
        ], [
            'contractor_profile_id.required' => 'Contractor is required.',
            'contractor_profile_id.integer' => 'Contractor ID must be a valid integer.',
            'contractor_profile_id.exists' => 'Selected contractor does not exist.',
            'service_charges.required' => 'At least one service charge is required.',
            'service_charges.array' => 'Service charges must be an array.',
            'service_charges.min' => 'At least one service charge is required.',
            'service_charges.*.amount.required' => 'Amount is required for all service charges.',
            'service_charges.*.amount.numeric' => 'Amount must be a valid number.',
            'service_charges.*.amount.min' => 'Amount must be greater than or equal to 0.',
            'service_charges.*.upper_bound.numeric' => 'Upper bound must be a valid number.',
            'service_charges.*.upper_bound.min' => 'Upper bound must be greater than or equal to 0.',
            'service_charges.*.lower_bound.numeric' => 'Lower bound must be a valid number.',
            'service_charges.*.lower_bound.min' => 'Lower bound must be greater than or equal to 0.',
            'service_charges.*.type.required' => 'Type is required for all service charges.',
            'service_charges.*.type.in' => 'Type must be either fixed or percentage.',
        ]);

        // Add custom validation for bounds and percentage limits
        $validator->after(function ($validator) use ($request) {
            foreach ($request->service_charges as $index => $chargeData) {
                // Validate upper_bound > lower_bound if both are provided
                if (isset($chargeData['upper_bound']) && isset($chargeData['lower_bound']) && 
                    $chargeData['upper_bound'] !== null && $chargeData['lower_bound'] !== null) {
                    if ($chargeData['upper_bound'] <= $chargeData['lower_bound']) {
                        $validator->errors()->add("service_charges.{$index}.upper_bound", 'Upper bound must be greater than lower bound.');
                    }
                }
                
                // Validate percentage limits
                if (isset($chargeData['type']) && $chargeData['type'] === 'percentage') {
                    if (isset($chargeData['amount']) && $chargeData['amount'] > 100) {
                        $validator->errors()->add("service_charges.{$index}.amount", 'Percentage amount cannot exceed 100%.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $businessId = $user->business_id;

        // Validate contractor exists and belongs to the business (for non-super admins)
        if ($businessId === 1) {
            // Super admin can select any contractor
            $contractor = ContractorProfile::where('id', $request->contractor_profile_id)->first();
        } else {
            // Regular users can only select contractors from their business
            $contractor = ContractorProfile::where('id', $request->contractor_profile_id)
                ->where('business_id', $businessId)
                ->first();
        }

        if (!$contractor) {
            return redirect()->back()
                ->withErrors(['contractor_profile_id' => 'Selected contractor not found or does not belong to your business.'])
                ->withInput();
        }

        // Create service charges
        foreach ($request->service_charges as $chargeData) {
            ContractorServiceCharge::create([
                'contractor_profile_id' => $request->contractor_profile_id,
                'amount' => $chargeData['amount'],
                'upper_bound' => $chargeData['upper_bound'] ?? null,
                'lower_bound' => $chargeData['lower_bound'] ?? null,
                'type' => $chargeData['type'],
                'business_id' => $contractor->business_id,
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route('contractor-service-charges.index')
            ->with('success', 'Contractor service charges created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractorServiceCharge $contractorServiceCharge)
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $this->authorizeContractorServiceCharge($contractorServiceCharge);
        
        return view('contractor-service-charges.show', compact('contractorServiceCharge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContractorServiceCharge $contractorServiceCharge)
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $this->authorizeContractorServiceCharge($contractorServiceCharge);

        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available contractors for the current business
        // Super admins (business_id = 1) can see all contractors
        if ($businessId === 1) {
            $contractors = ContractorProfile::with(['user', 'business'])->get();
        } else {
            $contractors = ContractorProfile::where('business_id', $businessId)
                ->with(['user', 'business'])
                ->get();
        }

        return view('contractor-service-charges.edit', compact('contractorServiceCharge', 'contractors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractorServiceCharge $contractorServiceCharge)
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $this->authorizeContractorServiceCharge($contractorServiceCharge);

        $validator = Validator::make($request->all(), [
            'contractor_profile_id' => 'required|integer|exists:contractor_profiles,id',
            'amount' => 'required|numeric|min:0',
            'upper_bound' => 'nullable|numeric|min:0',
            'lower_bound' => 'nullable|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'contractor_profile_id.required' => 'Contractor is required.',
            'contractor_profile_id.integer' => 'Contractor ID must be a valid integer.',
            'contractor_profile_id.exists' => 'Selected contractor does not exist.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'upper_bound.numeric' => 'Upper bound must be a valid number.',
            'upper_bound.min' => 'Upper bound must be greater than or equal to 0.',
            'lower_bound.numeric' => 'Lower bound must be a valid number.',
            'lower_bound.min' => 'Lower bound must be greater than or equal to 0.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type must be either fixed or percentage.',
            'description.string' => 'Description must be a valid string.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ]);

        // Add custom validation for bounds and percentage limits
        $validator->after(function ($validator) use ($request) {
            // Validate upper_bound > lower_bound if both are provided
            if ($request->upper_bound !== null && $request->lower_bound !== null) {
                if ($request->upper_bound <= $request->lower_bound) {
                    $validator->errors()->add('upper_bound', 'Upper bound must be greater than lower bound.');
                }
            }
            
            // Validate percentage limits
            if ($request->type === 'percentage') {
                if ($request->amount > 100) {
                    $validator->errors()->add('amount', 'Percentage amount cannot exceed 100%.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $businessId = $user->business_id;

        // Validate contractor exists and belongs to the business (for non-super admins)
        if ($businessId === 1) {
            // Super admin can select any contractor
            $contractor = ContractorProfile::where('id', $request->contractor_profile_id)->first();
        } else {
            // Regular users can only select contractors from their business
            $contractor = ContractorProfile::where('id', $request->contractor_profile_id)
                ->where('business_id', $businessId)
                ->first();
        }

        if (!$contractor) {
            return redirect()->back()
                ->withErrors(['contractor_profile_id' => 'Selected contractor not found or does not belong to your business.'])
                ->withInput();
        }

        $contractorServiceCharge->update([
            'contractor_profile_id' => $request->contractor_profile_id,
            'amount' => $request->amount,
            'upper_bound' => $request->upper_bound,
            'lower_bound' => $request->lower_bound,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('contractor-service-charges.index')
            ->with('success', 'Contractor service charge updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractorServiceCharge $contractorServiceCharge)
    {
        if (!in_array('Manage Contractor Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to contractor service charges.');
        }
        
        $this->authorizeContractorServiceCharge($contractorServiceCharge);

        $contractorServiceCharge->delete();

        return redirect()->route('contractor-service-charges.index')
            ->with('success', 'Contractor service charge deleted successfully!');
    }

    /**
     * Authorize access to the contractor service charge.
     */
    private function authorizeContractorServiceCharge(ContractorServiceCharge $contractorServiceCharge)
    {
        $user = Auth::user();
        
        // Allow super admin (business_id = 1) to access all contractor service charges
        if ($user->business_id === 1) {
            return;
        }
        
        // For regular users, only allow access to contractor service charges from their business
        if ($contractorServiceCharge->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to contractor service charge');
        }
    }
}
