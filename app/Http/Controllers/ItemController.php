<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;
use App\Models\Item;
use App\Models\Branch;
use App\Models\BranchItemPrice;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('items.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Business logic: Only business_id == 1 can select business, others default to their business
        $canSelectBusiness = Auth::user()->business_id == 1;
        
        if ($canSelectBusiness) {
            $businesses = Business::where('id', '!=', 1)->get();
            // Default to first business for initial load
            $selectedBusinessId = $businesses->first() ? $businesses->first()->id : null;
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
            $selectedBusinessId = Auth::user()->business_id;
        }

        // Get data filtered by selected business (not user's business)
        $groups = Group::where('business_id', $selectedBusinessId)->get();
        $departments = Department::where('business_id', $selectedBusinessId)->get();
        $itemUnits = ItemUnit::where('business_id', $selectedBusinessId)->get();
        $servicePoints = ServicePoint::where('business_id', $selectedBusinessId)->get();
        $contractors = ContractorProfile::with('business')->where('business_id', $selectedBusinessId)->get();
        $branches = Branch::where('business_id', $selectedBusinessId)->get();

        return view('items.create', compact(
            'businesses', 
            'groups', 
            'departments', 
            'itemUnits', 
            'servicePoints', 
            'contractors',
            'branches',
            'canSelectBusiness',
            'selectedBusinessId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:items,code',
            'type' => 'required|in:service,good,package,bulk',
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id',
            'subgroup_id' => 'nullable|exists:groups,id',
            'department_id' => 'nullable|exists:departments,id',
            'uom_id' => 'nullable|exists:item_units,id',
            'service_point_id' => 'nullable|exists:service_points,id',
            'default_price' => 'required|numeric|min:0',
            'hospital_share' => 'required|integer|between:0,100',
            'contractor_account_id' => 'nullable|exists:contractor_profiles,id',
            'business_id' => 'required|exists:businesses,id',
            'other_names' => 'nullable|string',
            'pricing_type' => 'required|in:default,custom',
            'branch_prices' => 'nullable|array',
            'branch_prices.*.branch_id' => 'nullable|exists:branches,id',
            'branch_prices.*.price' => 'nullable|numeric|min:0',
        ]);

        // Set business_id based on user permissions
        if (Auth::user()->business_id != 1) {
            $validated['business_id'] = Auth::user()->business_id;
        }

        // Validate contractor selection when hospital share is not 100%
        if ($validated['hospital_share'] != 100 && empty($validated['contractor_account_id'])) {
            return back()->withErrors(['contractor_account_id' => 'Contractor is required when hospital share is not 100%']);
        }

        // Create the item
        $item = Item::create($validated);

        // Handle branch item prices only if custom pricing is selected
        if ($validated['pricing_type'] === 'custom' && isset($validated['branch_prices'])) {
            foreach ($validated['branch_prices'] as $branchPrice) {
                if (!empty($branchPrice['branch_id']) && !empty($branchPrice['price'])) {
                    BranchItemPrice::create([
                        'business_id' => $validated['business_id'],
                        'branch_id' => $branchPrice['branch_id'],
                        'item_id' => $item->id,
                        'price' => $branchPrice['price'],
                    ]);
                }
            }
        }

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        // Load all the relationships needed for the show view
        $item->load([
            'business',
            'group',
            'subgroup',
            'department',
            'itemUnit',
            'servicePoint',
            'contractor.business',
            'branchPrices.branch'
        ]);

        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // Business logic: Only business_id == 1 can select business, others default to their business
        $canSelectBusiness = Auth::user()->business_id == 1;
        
        if ($canSelectBusiness) {
            $businesses = Business::where('id', '!=', 1)->get();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
        }

        // Get data filtered by the item's business (not user's business)
        $selectedBusinessId = $item->business_id;
        $groups = Group::where('business_id', $selectedBusinessId)->get();
        $departments = Department::where('business_id', $selectedBusinessId)->get();
        $itemUnits = ItemUnit::where('business_id', $selectedBusinessId)->get();
        $servicePoints = ServicePoint::where('business_id', $selectedBusinessId)->get();
        $contractors = ContractorProfile::with('business')->where('business_id', $selectedBusinessId)->get();
        $branches = Branch::where('business_id', $selectedBusinessId)->get();
        
        // Get existing branch prices for this item
        $branchPrices = $item->branchPrices;

        return view('items.edit', compact(
            'item', 
            'businesses', 
            'groups', 
            'departments', 
            'itemUnits', 
            'servicePoints', 
            'contractors',
            'branches',
            'branchPrices',
            'canSelectBusiness',
            'selectedBusinessId'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:items,code,' . $item->id,
            'type' => 'required|in:service,good,package,bulk',
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id',
            'subgroup_id' => 'nullable|exists:groups,id',
            'department_id' => 'nullable|exists:departments,id',
            'uom_id' => 'nullable|exists:item_units,id',
            'service_point_id' => 'nullable|exists:service_points,id',
            'default_price' => 'required|numeric|min:0',
            'hospital_share' => 'required|integer|between:0,100',
            'contractor_account_id' => 'nullable|exists:contractor_profiles,id',
            'business_id' => 'required|exists:businesses,id',
            'other_names' => 'nullable|string',
            'pricing_type' => 'required|in:default,custom',
            'branch_prices' => 'nullable|array',
            'branch_prices.*.branch_id' => 'nullable|exists:branches,id',
            'branch_prices.*.price' => 'nullable|numeric|min:0',
        ]);

        // Set business_id based on user permissions
        if (Auth::user()->business_id != 1) {
            $validated['business_id'] = Auth::user()->business_id;
        }

        // Validate contractor selection when hospital share is not 100%
        if ($validated['hospital_share'] != 100 && empty($validated['contractor_account_id'])) {
            return back()->withErrors(['contractor_account_id' => 'Contractor is required when hospital share is not 100%']);
        }

        // Update the item
        $item->update($validated);

        // Handle branch item prices - delete existing and create new ones only if custom pricing is selected
        $item->branchPrices()->delete();
        
        if ($validated['pricing_type'] === 'custom' && isset($validated['branch_prices'])) {
            foreach ($validated['branch_prices'] as $branchPrice) {
                if (!empty($branchPrice['branch_id']) && !empty($branchPrice['price'])) {
                    BranchItemPrice::create([
                        'business_id' => $validated['business_id'],
                        'branch_id' => $branchPrice['branch_id'],
                        'item_id' => $item->id,
                        'price' => $branchPrice['price'],
                    ]);
                }
            }
        }

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get filtered data based on selected business (AJAX endpoint)
     */
    public function getFilteredData(Request $request)
    {
        $businessId = $request->input('business_id');
        
        if (!$businessId) {
            return response()->json([
                'groups' => [],
                'departments' => [],
                'itemUnits' => [],
                'servicePoints' => [],
                'contractors' => [],
                'branches' => []
            ]);
        }

        // Validate that the user has permission to access this business
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $businessId) {
            return response()->json(['error' => 'Unauthorized access to business data'], 403);
        }

        $data = [
            'groups' => Group::where('business_id', $businessId)->get(),
            'departments' => Department::where('business_id', $businessId)->get(),
            'itemUnits' => ItemUnit::where('business_id', $businessId)->get(),
            'servicePoints' => ServicePoint::where('business_id', $businessId)->get(),
            'contractors' => ContractorProfile::with('business')->where('business_id', $businessId)->get(),
            'branches' => Branch::where('business_id', $businessId)->get()
        ];

        return response()->json($data);
    }
}
