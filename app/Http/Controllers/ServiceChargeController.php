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
        return view('service-charges.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available businesses for selection (excluding the first business which is the super business)
        $businesses = Business::where('id', '!=', 1)->get();

        return view('service-charges.create', compact('businesses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_id' => 'required|integer',
            'service_charges' => 'required|array|min:1',
            'service_charges.*.amount' => 'required|numeric|min:0',
            'service_charges.*.upper_bound' => 'nullable|numeric|min:0',
            'service_charges.*.lower_bound' => 'nullable|numeric|min:0',
            'service_charges.*.type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $businessId = $user->business_id;

        // Validate business exists and is not the super business
        $business = Business::findOrFail($request->entity_id);
        if ($business->id === 1) {
            abort(403, 'Cannot create service charges for the super business.');
        }

        // Create service charges
        foreach ($request->service_charges as $chargeData) {
            ServiceCharge::create([
                'entity_type' => 'business',
                'entity_id' => $request->entity_id,
                'amount' => $chargeData['amount'],
                'upper_bound' => $chargeData['upper_bound'] ?? null,
                'lower_bound' => $chargeData['lower_bound'] ?? null,
                'type' => $chargeData['type'],
                'business_id' => $request->entity_id, // Use the selected business ID
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
        $this->authorizeServiceCharge($serviceCharge);
        
        return view('service-charges.show', compact('serviceCharge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCharge $serviceCharge)
    {
        $this->authorizeServiceCharge($serviceCharge);

        $user = Auth::user();
        $businessId = $user->business_id;

        // Get available entities for selection
        $businesses = Business::where('id', $businessId)->get();
        $branches = Branch::where('business_id', $businessId)->get();
        $servicePoints = ServicePoint::where('business_id', $businessId)->get();

        return view('service-charges.edit', compact('serviceCharge', 'businesses', 'branches', 'servicePoints'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCharge $serviceCharge)
    {
        $this->authorizeServiceCharge($serviceCharge);

        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:business,branch,service_point',
            'entity_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $businessId = $user->business_id;

        // Validate entity exists and belongs to the business
        $this->validateEntity($request->entity_type, $request->entity_id, $businessId);

        $serviceCharge->update([
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('service-charges.index')
            ->with('success', 'Service charge updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCharge $serviceCharge)
    {
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
     * Authorize access to the service charge.
     */
    private function authorizeServiceCharge(ServiceCharge $serviceCharge)
    {
        $user = Auth::user();
        
        if ($serviceCharge->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to service charge');
        }
    }
}
