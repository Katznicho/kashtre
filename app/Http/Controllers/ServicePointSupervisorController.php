<?php

namespace App\Http\Controllers;

use App\Models\ServicePointSupervisor;
use App\Models\ServicePoint;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicePointSupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // If user is from Kashtre (business_id = 1), show all supervisors
        // Otherwise, show only supervisors for their business
        if (Auth::user()->business_id == 1) {
            $supervisors = ServicePointSupervisor::with(['servicePoint', 'supervisor', 'business'])
                ->orderBy('business_id')
                ->orderBy('service_point_id')
                ->get();
        } else {
            $supervisors = ServicePointSupervisor::with(['servicePoint', 'supervisor', 'business'])
                ->where('business_id', Auth::user()->business_id)
                ->orderBy('service_point_id')
                ->get();
        }

        return view('settings.service-point-supervisors.index', compact('supervisors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // If user is from Kashtre, show all businesses and service points
        // Otherwise, show only their business's service points
        if (Auth::user()->business_id == 1) {
            $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
            $servicePoints = ServicePoint::with('business')->orderBy('business_id')->orderBy('name')->get();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
            $servicePoints = ServicePoint::where('business_id', Auth::user()->business_id)
                ->orderBy('name')
                ->get();
        }

        // Get users for the selected business (or all if Kashtre)
        $users = User::where('status', 'active')
            ->when(Auth::user()->business_id != 1, function ($query) {
                return $query->where('business_id', Auth::user()->business_id);
            })
            ->orderBy('name')
            ->get();

        return view('settings.service-point-supervisors.create', compact('businesses', 'servicePoints', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_point_id' => 'required|exists:service_points,id|unique:service_point_supervisors,service_point_id',
            'supervisor_user_id' => 'required|exists:users,id',
        ]);

        // Get the service point to determine business_id
        $servicePoint = ServicePoint::findOrFail($validated['service_point_id']);
        $validated['business_id'] = $servicePoint->business_id;

        // Check if user has permission (only allow if from same business or Kashtre)
        if (Auth::user()->business_id != 1 && $validated['business_id'] != Auth::user()->business_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You do not have permission to assign supervisors for other businesses.');
        }

        ServicePointSupervisor::create($validated);

        return redirect()->route('service-point-supervisors.index')
            ->with('success', 'Service point supervisor assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServicePointSupervisor $servicePointSupervisor)
    {
        $servicePointSupervisor->load(['servicePoint', 'supervisor', 'business']);
        return view('settings.service-point-supervisors.show', compact('servicePointSupervisor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServicePointSupervisor $servicePointSupervisor)
    {
        // Check if user has permission
        if (Auth::user()->business_id != 1 && $servicePointSupervisor->business_id != Auth::user()->business_id) {
            abort(403, 'Access denied.');
        }

        if (Auth::user()->business_id == 1) {
            $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
            $servicePoints = ServicePoint::with('business')->orderBy('business_id')->orderBy('name')->get();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
            $servicePoints = ServicePoint::where('business_id', Auth::user()->business_id)
                ->orderBy('name')
                ->get();
        }

        $users = User::where('status', 'active')
            ->when(Auth::user()->business_id != 1, function ($query) {
                return $query->where('business_id', Auth::user()->business_id);
            })
            ->orderBy('name')
            ->get();

        return view('settings.service-point-supervisors.edit', compact('servicePointSupervisor', 'businesses', 'servicePoints', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServicePointSupervisor $servicePointSupervisor)
    {
        // Check if user has permission
        if (Auth::user()->business_id != 1 && $servicePointSupervisor->business_id != Auth::user()->business_id) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'service_point_id' => 'required|exists:service_points,id|unique:service_point_supervisors,service_point_id,' . $servicePointSupervisor->id,
            'supervisor_user_id' => 'required|exists:users,id',
        ]);

        // Get the service point to determine business_id
        $servicePoint = ServicePoint::findOrFail($validated['service_point_id']);
        $validated['business_id'] = $servicePoint->business_id;

        $servicePointSupervisor->update($validated);

        return redirect()->route('service-point-supervisors.index')
            ->with('success', 'Service point supervisor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicePointSupervisor $servicePointSupervisor)
    {
        // Check if user has permission
        if (Auth::user()->business_id != 1 && $servicePointSupervisor->business_id != Auth::user()->business_id) {
            abort(403, 'Access denied.');
        }

        $servicePointSupervisor->delete();

        return redirect()->route('service-point-supervisors.index')
            ->with('success', 'Service point supervisor removed successfully.');
    }
}
