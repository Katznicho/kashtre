<?php

namespace App\Http\Controllers;

use App\Models\ServiceCharge;
use App\Models\Business;
use App\Models\Branch;
use App\Models\ServicePoint;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        return view('service-charges.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available businesses for selection (excluding the first business which is the super business)
        // For non-super admins, only show their own business
        if ($user->business_id === 1) {
            $businesses = Business::where('id', '!=', 1)->get();
        } else {
            $businesses = Business::where('id', $businessId)->get();
        }

        return view('service-charges.create', compact('businesses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $validator = Validator::make($request->all(), [
            'entity_id' => 'required|integer|exists:businesses,id',
            'service_charges' => 'required|array|min:1',
            'service_charges.*.amount' => 'required|numeric|min:0',
            'service_charges.*.upper_bound' => 'required|numeric|min:0',
            'service_charges.*.lower_bound' => 'required|numeric|min:0',
            'service_charges.*.type' => 'required|in:fixed,percentage',
        ], [
            'service_charges.*.amount.required' => 'Amount is required for all service charges.',
            'service_charges.*.amount.numeric' => 'Amount must be a valid number.',
            'service_charges.*.amount.min' => 'Amount must be greater than or equal to 0.',
            'service_charges.*.upper_bound.required' => 'Upper bound is required for all service charges.',
            'service_charges.*.upper_bound.numeric' => 'Upper bound must be a valid number.',
            'service_charges.*.upper_bound.min' => 'Upper bound must be greater than or equal to 0.',
            'service_charges.*.lower_bound.required' => 'Lower bound is required for all service charges.',
            'service_charges.*.lower_bound.numeric' => 'Lower bound must be a valid number.',
            'service_charges.*.lower_bound.min' => 'Lower bound must be greater than or equal to 0.',
            'service_charges.*.type.required' => 'Type is required for all service charges.',
            'service_charges.*.type.in' => 'Type must be either fixed or percentage.',
        ]);

        // Add custom validation for bounds and percentage limits
        $validator->after(function ($validator) use ($request) {
            foreach ($request->service_charges as $index => $chargeData) {
                // Validate upper_bound > lower_bound
                if (isset($chargeData['upper_bound']) && isset($chargeData['lower_bound'])) {
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

        // Validate business exists and belongs to the user's business (for non-super admins)
        if ($user->business_id !== 1) {
            $business = Business::where('id', $request->entity_id)
                ->where('id', $businessId)
                ->first();
            
            if (!$business) {
                                 return redirect()->back()
                     ->withErrors(['entity_id' => 'Selected entity not found or does not belong to your business.'])
                     ->withInput();
            }
        }

        // Create service charges
        foreach ($request->service_charges as $chargeData) {
            ServiceCharge::create([
                'entity_type' => 'business',
                'entity_id' => $request->entity_id,
                'amount' => $chargeData['amount'],
                'upper_bound' => $chargeData['upper_bound'],
                'lower_bound' => $chargeData['lower_bound'],
                'type' => $chargeData['type'],
                'business_id' => $request->entity_id,
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route('service-charges.index')
            ->with('success', 'Service charges created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceCharge $serviceCharge)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $this->authorizeServiceCharge($serviceCharge);
        
        return view('service-charges.show', compact('serviceCharge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCharge $serviceCharge)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $this->authorizeServiceCharge($serviceCharge);

        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available businesses for selection (excluding super business)
        $businesses = Business::where('id', '!=', 1)->get();

        return view('service-charges.edit', compact('serviceCharge', 'businesses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCharge $serviceCharge)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $this->authorizeServiceCharge($serviceCharge);

        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:business,branch,service_point',
            'entity_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'upper_bound' => 'nullable|numeric|min:0',
            'lower_bound' => 'nullable|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'entity_type.required' => 'Entity type is required.',
            'entity_type.in' => 'Entity type must be business, branch, or service_point.',
            'entity_id.required' => 'Entity ID is required.',
            'entity_id.integer' => 'Entity ID must be a valid integer.',
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

        // Validate entity exists and belongs to the business
        $this->validateEntity($request->entity_type, $request->entity_id, $businessId);

        // Determine the business_id for the service charge
        $serviceChargeBusinessId = $this->getBusinessIdForEntity($request->entity_type, $request->entity_id);

        $serviceCharge->update([
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'amount' => $request->amount,
            'upper_bound' => $request->upper_bound,
            'lower_bound' => $request->lower_bound,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'business_id' => $serviceChargeBusinessId,
        ]);

        return redirect()->route('service-charges.index')
            ->with('success', 'Service charge updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCharge $serviceCharge)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $this->authorizeServiceCharge($serviceCharge);

        $serviceCharge->delete();

        return redirect()->route('service-charges.index')
            ->with('success', 'Service charge deleted successfully!');
    }

    /**
     * Get entities based on entity type for AJAX requests.
     */
    public function getEntities(Request $request)
    {
        if (!in_array('Manage Service Charges', Auth::user()->permissions)) {
            abort(403, 'Unauthorized access to service charges.');
        }
        
        $user = Auth::user();
        $businessId = $user->business_id;
        $entityType = $request->entity_type;

        $entities = [];

        switch ($entityType) {
            case 'business':
                $entities = Business::where('id', $businessId)
                    ->select('id', 'name')
                    ->get();
                break;
            case 'branch':
                $entities = Branch::where('business_id', $businessId)
                    ->select('id', 'name')
                    ->get();
                break;
            case 'service_point':
                $entities = ServicePoint::where('business_id', $businessId)
                    ->select('id', 'name')
                    ->get();
                break;

        }

        return response()->json($entities);
    }

    /**
     * Validate that the entity exists and belongs to the business.
     */
    private function validateEntity($entityType, $entityId, $businessId)
    {
        switch ($entityType) {
            case 'business':
                $entity = Business::where('id', $entityId)->first();
                break;
            case 'branch':
                $entity = Branch::where('id', $entityId)
                    ->where('business_id', $businessId)
                    ->first();
                break;
            case 'service_point':
                $entity = ServicePoint::where('id', $entityId)
                    ->where('business_id', $businessId)
                    ->first();
                break;

            default:
                throw new \InvalidArgumentException('Invalid entity type');
        }

        if (!$entity) {
            throw new \InvalidArgumentException('Selected entity not found or does not belong to your business');
        }
    }

    /**
     * Get the business ID for a given entity.
     */
    private function getBusinessIdForEntity($entityType, $entityId)
    {
        switch ($entityType) {
            case 'business':
                return $entityId;
            case 'branch':
                $branch = Branch::find($entityId);
                return $branch ? $branch->business_id : null;
            case 'service_point':
                $servicePoint = ServicePoint::find($entityId);
                return $servicePoint ? $servicePoint->business_id : null;

            default:
                throw new \InvalidArgumentException('Invalid entity type');
        }
    }

    /**
     * Authorize access to the service charge.
     */
    private function authorizeServiceCharge(ServiceCharge $serviceCharge)
    {
        $user = Auth::user();
        
        // Allow super admin (business_id = 1) to access all service charges
        if ($user->business_id === 1) {
            return;
        }
        
        // For regular users, only allow access to service charges from their business
        if ($serviceCharge->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to service charge');
        }
    }
}
