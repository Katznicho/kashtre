<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('items.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $businesses = \App\Models\Business::where('id', '!=', 1)->get();
        $groups = \App\Models\Group::all();
        $departments = \App\Models\Department::all();
        $itemUnits = \App\Models\ItemUnit::all();
        $servicePoints = \App\Models\ServicePoint::all();
        $contractors = \App\Models\ContractorProfile::all();

        return view('items.create', compact('businesses', 'groups', 'departments', 'itemUnits', 'servicePoints', 'contractors'));
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
        ]);

        \App\Models\Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Item $item)
    {
        $businesses = \App\Models\Business::where('id', '!=', 1)->get();
        $groups = \App\Models\Group::all();
        $departments = \App\Models\Department::all();
        $itemUnits = \App\Models\ItemUnit::all();
        $servicePoints = \App\Models\ServicePoint::all();
        $contractors = \App\Models\ContractorProfile::all();

        return view('items.edit', compact('item', 'businesses', 'groups', 'departments', 'itemUnits', 'servicePoints', 'contractors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Item $item)
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
        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
