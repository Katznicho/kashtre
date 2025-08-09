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
use App\Models\PackageItem;
use App\Models\BulkItem;
use App\Models\BranchServicePoint;

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
        
        // Get available items for package and bulk selection (exclude package and bulk types)
        $availableItems = Item::where('business_id', $selectedBusinessId)
            ->whereNotIn('type', ['package', 'bulk'])
            ->get();

        return view('items.create', compact(
            'businesses', 
            'groups', 
            'departments', 
            'itemUnits', 
            'servicePoints', 
            'contractors',
            'branches',
            'availableItems',
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
            'code' => 'nullable|string|unique:items,code',
            'type' => 'required|in:service,good,package,bulk',
            'description' => 'nullable|string',
            'group_id' => 'required_unless:type,package,bulk|nullable|exists:groups,id',
            'subgroup_id' => 'required_if:type,service,good|nullable|exists:groups,id',
            'department_id' => 'required_if:type,service,good|nullable|exists:departments,id',
            'uom_id' => 'required_unless:type,package,bulk|nullable|exists:item_units,id',
            'default_price' => 'required|numeric|min:0',
            'hospital_share' => 'required_if:type,service,good|integer|between:0,100',
            'contractor_account_id' => 'nullable|exists:contractor_profiles,id',
            'business_id' => 'required|exists:businesses,id',
            'other_names' => 'required|string',
            'validity_days' => 'nullable|integer|min:1',
            'pricing_type' => 'required|in:default,custom',
            'branch_prices' => 'nullable|array',
            'branch_prices.*.branch_id' => 'nullable|exists:branches,id',
            'branch_prices.*.price' => 'nullable|numeric|min:0',
            'branch_service_points' => 'nullable|array',
            'branch_service_points.*' => 'nullable|exists:service_points,id',
            'package_items' => 'nullable|array',
            'package_items.*.included_item_id' => 'nullable|exists:items,id',
            'package_items.*.max_quantity' => 'nullable|integer|min:1',
            'package_items.*.validity_days' => 'nullable|integer|min:1',
            'bulk_items' => 'nullable|array',
            'bulk_items.*.included_item_id' => 'nullable|exists:items,id',
            'bulk_items.*.fixed_quantity' => 'nullable|integer|min:1',
        ]);

        // Set business_id based on user permissions
        if (Auth::user()->business_id != 1) {
            $validated['business_id'] = Auth::user()->business_id;
        }

        // Set hospital_share to 100 for package and bulk types
        if (in_array($validated['type'], ['package', 'bulk'])) {
            $validated['hospital_share'] = 100;
            $validated['contractor_account_id'] = null;
            // Set service/good specific fields to null for packages and bulk items
            $validated['group_id'] = null;
            $validated['subgroup_id'] = null;
            $validated['department_id'] = null;
            $validated['uom_id'] = null;
        }

        // Validate contractor selection when hospital share is not 100% for goods and services
        if (in_array($validated['type'], ['service', 'good']) && $validated['hospital_share'] != 100 && empty($validated['contractor_account_id'])) {
            return back()->withErrors(['contractor_account_id' => 'Contractor is required when hospital share is not 100%']);
        }

        // Validate that at least one branch has a custom price when custom pricing is selected
        if ($validated['pricing_type'] === 'custom' && isset($validated['branch_prices'])) {
            $branches = Branch::where('business_id', $validated['business_id'])->get();
            $providedPrices = collect($validated['branch_prices'])->pluck('price', 'branch_id');
            
            // Check if at least one branch has a custom price
            $hasCustomPrices = false;
            foreach ($branches as $branch) {
                if ($providedPrices->has($branch->id) && !empty($providedPrices->get($branch->id))) {
                    $hasCustomPrices = true;
                    break;
                }
            }
            
            if (!$hasCustomPrices) {
                return back()->withErrors(['branch_prices' => 'At least one branch must have a custom price when custom pricing is selected']);
            }
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

        // Handle branch service points
        if (isset($validated['branch_service_points'])) {
            foreach ($validated['branch_service_points'] as $branchId => $servicePointId) {
                if (!empty($servicePointId)) {
                    BranchServicePoint::create([
                        'business_id' => $validated['business_id'],
                        'branch_id' => $branchId,
                        'service_point_id' => $servicePointId,
                        'item_id' => $item->id,
                    ]);
                }
            }
        }

        // Handle package items
        if ($validated['type'] === 'package' && isset($validated['package_items'])) {
            foreach ($validated['package_items'] as $packageItem) {
                if (!empty($packageItem['included_item_id'])) {
                    PackageItem::create([
                        'package_item_id' => $item->id,
                        'included_item_id' => $packageItem['included_item_id'],
                        'max_quantity' => $packageItem['max_quantity'] ?? 1,
                        'business_id' => $validated['business_id'],
                    ]);
                }
            }
        }

        // Handle bulk items
        if ($validated['type'] === 'bulk' && isset($validated['bulk_items'])) {
            foreach ($validated['bulk_items'] as $bulkItem) {
                if (!empty($bulkItem['included_item_id'])) {
                    BulkItem::create([
                        'bulk_item_id' => $item->id,
                        'included_item_id' => $bulkItem['included_item_id'],
                        'fixed_quantity' => $bulkItem['fixed_quantity'] ?? 1,
                        'business_id' => $validated['business_id'],
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
            'contractor.business',
            'branchServicePoints.branch',
            'branchServicePoints.servicePoint',
            'branchPrices.branch',
            'packageItems.includedItem',
            'bulkItems.includedItem',
            'includedInPackages.packageItem',
            'includedInBulks.bulkItem'
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
        
        // Get existing branch service points for this item
        $branchServicePoints = $item->branchServicePoints;
        
        // Get existing package and bulk items
        $packageItems = $item->packageItems;
        $bulkItems = $item->bulkItems;
        
        // Get available items for package and bulk selection (exclude package and bulk types, and current item)
        $availableItems = Item::where('business_id', $selectedBusinessId)
            ->whereNotIn('type', ['package', 'bulk'])
            ->where('id', '!=', $item->id)
            ->get();

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
            'branchServicePoints',
            'packageItems',
            'bulkItems',
            'availableItems',
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
            'code' => 'nullable|string|unique:items,code,' . $item->id,
            'type' => 'required|in:service,good,package,bulk',
            'description' => 'nullable|string',
            'group_id' => 'required_unless:type,package,bulk|nullable|exists:groups,id',
            'subgroup_id' => 'required_if:type,service,good|nullable|exists:groups,id',
            'department_id' => 'required_if:type,service,good|nullable|exists:departments,id',
            'uom_id' => 'required_unless:type,package,bulk|nullable|exists:item_units,id',
            'default_price' => 'required|numeric|min:0',
            'hospital_share' => 'required_if:type,service,good|integer|between:0,100',
            'contractor_account_id' => 'nullable|exists:contractor_profiles,id',
            'business_id' => 'required|exists:businesses,id',
            'other_names' => 'nullable|string',
            'pricing_type' => 'required|in:default,custom',
            'branch_prices' => 'nullable|array',
            'branch_prices.*.branch_id' => 'nullable|exists:branches,id',
            'branch_prices.*.price' => 'nullable|numeric|min:0',
            'branch_service_points' => 'nullable|array',
            'branch_service_points.*' => 'nullable|exists:service_points,id',
            'package_items' => 'nullable|array',
            'package_items.*.included_item_id' => 'nullable|exists:items,id',
            'package_items.*.max_quantity' => 'nullable|integer|min:1',
            'package_items.*.validity_days' => 'nullable|integer|min:1',
            'bulk_items' => 'nullable|array',
            'bulk_items.*.included_item_id' => 'nullable|exists:items,id',
            'bulk_items.*.fixed_quantity' => 'nullable|integer|min:1',
        ]);

        // Set business_id based on user permissions
        if (Auth::user()->business_id != 1) {
            $validated['business_id'] = Auth::user()->business_id;
        }

        // Set hospital_share to 100 for package and bulk types
        if (in_array($validated['type'], ['package', 'bulk'])) {
            $validated['hospital_share'] = 100;
            $validated['contractor_account_id'] = null;
            // Set service/good specific fields to null for packages and bulk items
            $validated['group_id'] = null;
            $validated['subgroup_id'] = null;
            $validated['department_id'] = null;
            $validated['uom_id'] = null;
        }

        // Validate contractor selection when hospital share is not 100% for goods and services
        if (in_array($validated['type'], ['service', 'good']) && $validated['hospital_share'] != 100 && empty($validated['contractor_account_id'])) {
            return back()->withErrors(['contractor_account_id' => 'Contractor is required when hospital share is not 100%']);
        }

        // Validate that at least one branch has a custom price when custom pricing is selected
        if ($validated['pricing_type'] === 'custom' && isset($validated['branch_prices'])) {
            $branches = Branch::where('business_id', $validated['business_id'])->get();
            $providedPrices = collect($validated['branch_prices'])->pluck('price', 'branch_id');
            
            // Check if at least one branch has a custom price
            $hasCustomPrices = false;
            foreach ($branches as $branch) {
                if ($providedPrices->has($branch->id) && !empty($providedPrices->get($branch->id))) {
                    $hasCustomPrices = true;
                    break;
                }
            }
            
            if (!$hasCustomPrices) {
                return back()->withErrors(['branch_prices' => 'At least one branch must have a custom price when custom pricing is selected']);
            }
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

        // Handle branch service points - delete existing and create new ones
        $item->branchServicePoints()->delete();

        if (isset($validated['branch_service_points'])) {
            foreach ($validated['branch_service_points'] as $branchId => $servicePointId) {
                if (!empty($servicePointId)) {
                    BranchServicePoint::create([
                        'business_id' => $validated['business_id'],
                        'branch_id' => $branchId,
                        'service_point_id' => $servicePointId,
                        'item_id' => $item->id,
                    ]);
                }
            }
        }

        // Handle package items - delete existing and create new ones
        $item->packageItems()->delete();
        
        if ($validated['type'] === 'package' && isset($validated['package_items'])) {
            foreach ($validated['package_items'] as $packageItem) {
                if (!empty($packageItem['included_item_id'])) {
                    PackageItem::create([
                        'package_item_id' => $item->id,
                        'included_item_id' => $packageItem['included_item_id'],
                        'max_quantity' => $packageItem['max_quantity'] ?? 1,
                        'business_id' => $validated['business_id'],
                    ]);
                }
            }
        }

        // Handle bulk items - delete existing and create new ones
        $item->bulkItems()->delete();
        
        if ($validated['type'] === 'bulk' && isset($validated['bulk_items'])) {
            foreach ($validated['bulk_items'] as $bulkItem) {
                if (!empty($bulkItem['included_item_id'])) {
                    BulkItem::create([
                        'bulk_item_id' => $item->id,
                        'included_item_id' => $bulkItem['included_item_id'],
                        'fixed_quantity' => $bulkItem['fixed_quantity'] ?? 1,
                        'business_id' => $validated['business_id'],
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

        // Get groups
        $groups = Group::where('business_id', $businessId)->get();

        // Get departments
        $departments = Department::where('business_id', $businessId)->get();

        // Get item units
        $itemUnits = ItemUnit::where('business_id', $businessId)->get();

        // Get service points grouped by branches
        $servicePoints = ServicePoint::where('business_id', $businessId)
            ->with('branch')
            ->get()
            ->groupBy('branch_id');

        // Get contractors
        $contractors = ContractorProfile::with('business')->where('business_id', $businessId)->get();

        // Get branches
        $branches = Branch::where('business_id', $businessId)->get();

        return response()->json([
            'groups' => $groups,
            'departments' => $departments,
            'itemUnits' => $itemUnits,
            'servicePoints' => $servicePoints,
            'contractors' => $contractors,
            'branches' => $branches
        ]);
    }

    /**
     * Generate a unique item code for the given business (AJAX endpoint)
     */
    public function generateCode(Request $request)
    {
        $businessId = $request->input('business_id');
        
        if (!$businessId) {
            return response()->json(['error' => 'Business ID is required'], 400);
        }

        // Validate that the user has permission to access this business
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $businessId) {
            return response()->json(['error' => 'Unauthorized access to business data'], 403);
        }

        $code = Item::generateUniqueCode($businessId);
        
        return response()->json(['code' => $code]);
    }
}
