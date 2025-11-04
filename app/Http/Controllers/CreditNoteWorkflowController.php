<?php

namespace App\Http\Controllers;

use App\Models\CreditNoteWorkflow;
use App\Models\Business;
use App\Models\User;
use App\Models\ServicePoint;
use App\Models\ServicePointSupervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditNoteWorkflowController extends Controller
{
    public function __construct()
    {
        // Only allow Kashtre users (business_id = 1) with proper permissions to access these settings
        $this->middleware(function ($request, $next) {
            if (auth()->user()->business_id !== 1) {
                abort(403, 'Access denied. This feature is only available to Kashtre administrators.');
            }
            
            // Check for View Credit Note Workflows permission
            if (!in_array('View Credit Note Workflows', auth()->user()->permissions ?? [])) {
                abort(403, 'Access denied. You do not have permission to view credit note workflows.');
            }
            
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.credit-note-workflows.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check for Add Credit Note Workflows permission
        if (!in_array('Add Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to add credit note workflows.');
        }

        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        
        // Get all users from all businesses for dropdowns with business_id
        $allUsers = User::where('business_id', '!=', 1)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'business_id']);

        // Get all service points with business_id for client-side filtering
        $allServicePoints = ServicePoint::where('business_id', '!=', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'business_id']);

        return view('settings.credit-note-workflows.create', compact('businesses', 'allUsers', 'allServicePoints'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check for Add Credit Note Workflows permission
        if (!in_array('Add Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to add credit note workflows.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|unique:credit_note_workflows,business_id',
            'default_supervisor_user_id' => 'nullable|exists:users,id',
            'finance_user_id' => 'nullable|exists:users,id',
            'ceo_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'service_point_supervisors' => 'nullable|array',
            'service_point_supervisors.*.service_point_id' => 'required|exists:service_points,id',
            'service_point_supervisors.*.supervisor_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        DB::beginTransaction();
        try {
            // Create the workflow
            $workflow = CreditNoteWorkflow::create([
                'business_id' => $validated['business_id'],
                'default_supervisor_user_id' => $validated['default_supervisor_user_id'] ?? null,
                'finance_user_id' => $validated['finance_user_id'] ?? null,
                'ceo_user_id' => $validated['ceo_user_id'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            // Handle service point supervisors
            // Supervisors can reassign "in progress" and "partially done" items to other users
            if (isset($validated['service_point_supervisors'])) {
                foreach ($validated['service_point_supervisors'] as $spData) {
                    $servicePointId = $spData['service_point_id'];
                    $supervisorUserId = $spData['supervisor_user_id'] ?? null;
                    
                    // If no specific supervisor is set, use default (or null if no default)
                    if (empty($supervisorUserId)) {
                        $supervisorUserId = $validated['default_supervisor_user_id'] ?? null;
                    }
                    
                    // Check if supervisor already exists for this service point
                    $existingSupervisor = ServicePointSupervisor::where('service_point_id', $servicePointId)
                        ->where('business_id', $validated['business_id'])
                        ->first();
                    
                    if ($supervisorUserId) {
                        // We have a supervisor (specific or default)
                        if ($existingSupervisor) {
                            // Update existing supervisor
                            $existingSupervisor->update([
                                'supervisor_user_id' => $supervisorUserId,
                            ]);
                        } else {
                            // Create new supervisor assignment
                            ServicePointSupervisor::create([
                                'service_point_id' => $servicePointId,
                                'supervisor_user_id' => $supervisorUserId,
                                'business_id' => $validated['business_id'],
                            ]);
                        }
                    } else {
                        // No supervisor assigned (neither specific nor default) - remove existing if any
                        if ($existingSupervisor) {
                            $existingSupervisor->delete();
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('credit-note-workflows.index')
                ->with('success', 'Credit note workflow created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create workflow: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CreditNoteWorkflow $creditNoteWorkflow)
    {
        $creditNoteWorkflow->load(['business', 'defaultSupervisor', 'finance', 'ceo']);
        
        // Get service points for the workflow's business
        $servicePoints = ServicePoint::where('business_id', $creditNoteWorkflow->business_id)
            ->orderBy('name')
            ->get();

        // Get existing service point supervisors
        $servicePointSupervisors = ServicePointSupervisor::where('business_id', $creditNoteWorkflow->business_id)
            ->with('supervisor')
            ->get()
            ->keyBy('service_point_id');

        return view('settings.credit-note-workflows.show', compact('creditNoteWorkflow', 'servicePoints', 'servicePointSupervisors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CreditNoteWorkflow $creditNoteWorkflow)
    {
        // Check for Edit Credit Note Workflows permission
        if (!in_array('Edit Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to edit credit note workflows.');
        }

        $businesses = Business::where('id', '!=', 1)->orderBy('name')->get();
        
        // Get all users from all businesses for dropdowns with business_id
        $allUsers = User::where('business_id', '!=', 1)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'business_id']);

        // Get service points for the workflow's business
        $servicePoints = ServicePoint::where('business_id', $creditNoteWorkflow->business_id)
            ->orderBy('name')
            ->get();

        // Get existing service point supervisors
        $servicePointSupervisors = ServicePointSupervisor::where('business_id', $creditNoteWorkflow->business_id)
            ->get()
            ->keyBy('service_point_id');

        return view('settings.credit-note-workflows.edit', compact('creditNoteWorkflow', 'businesses', 'allUsers', 'servicePoints', 'servicePointSupervisors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CreditNoteWorkflow $creditNoteWorkflow)
    {
        // Check for Edit Credit Note Workflows permission
        if (!in_array('Edit Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to edit credit note workflows.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|unique:credit_note_workflows,business_id,' . $creditNoteWorkflow->id,
            'default_supervisor_user_id' => 'nullable|exists:users,id',
            'finance_user_id' => 'nullable|exists:users,id',
            'ceo_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'service_point_supervisors' => 'nullable|array',
            'service_point_supervisors.*.service_point_id' => 'required|exists:service_points,id',
            'service_point_supervisors.*.supervisor_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        DB::beginTransaction();
        try {
            // Update the workflow
            $creditNoteWorkflow->update([
                'business_id' => $validated['business_id'],
                'default_supervisor_user_id' => $validated['default_supervisor_user_id'] ?? null,
                'finance_user_id' => $validated['finance_user_id'] ?? null,
                'ceo_user_id' => $validated['ceo_user_id'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            // Handle service point supervisors
            // Supervisors can reassign "in progress" and "partially done" items to other users
            if (isset($validated['service_point_supervisors'])) {
                foreach ($validated['service_point_supervisors'] as $spData) {
                    $servicePointId = $spData['service_point_id'];
                    $supervisorUserId = $spData['supervisor_user_id'] ?? null;
                    
                    // If no specific supervisor is set, use default (or null if no default)
                    if (empty($supervisorUserId)) {
                        $supervisorUserId = $validated['default_supervisor_user_id'] ?? null;
                    }
                    
                    // Check if supervisor already exists for this service point
                    $existingSupervisor = ServicePointSupervisor::where('service_point_id', $servicePointId)
                        ->where('business_id', $validated['business_id'])
                        ->first();
                    
                    if ($supervisorUserId) {
                        // We have a supervisor (specific or default)
                        if ($existingSupervisor) {
                            // Update existing supervisor
                            $existingSupervisor->update([
                                'supervisor_user_id' => $supervisorUserId,
                            ]);
                        } else {
                            // Create new supervisor assignment
                            ServicePointSupervisor::create([
                                'service_point_id' => $servicePointId,
                                'supervisor_user_id' => $supervisorUserId,
                                'business_id' => $validated['business_id'],
                            ]);
                        }
                    } else {
                        // No supervisor assigned (neither specific nor default) - remove existing if any
                        if ($existingSupervisor) {
                            $existingSupervisor->delete();
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('credit-note-workflows.index')
                ->with('success', 'Credit note workflow updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update workflow: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreditNoteWorkflow $creditNoteWorkflow)
    {
        // Check for Delete Credit Note Workflows permission
        if (!in_array('Delete Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to delete credit note workflows.');
        }

        $creditNoteWorkflow->delete();

        return redirect()->route('credit-note-workflows.index')
            ->with('success', 'Credit note workflow deleted successfully.');
    }
}
