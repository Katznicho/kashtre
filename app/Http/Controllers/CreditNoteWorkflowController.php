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
use Illuminate\Validation\ValidationException;

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
        if (!in_array('Add Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to add credit note workflows.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|unique:credit_note_workflows,business_id',
            'default_supervisor_user_id' => 'nullable|exists:users,id',
            'approver_user_ids' => 'required|array|min:1|max:3',
            'approver_user_ids.*' => 'integer|exists:users,id',
            'authorizer_user_ids' => 'required|array|min:1|max:3',
            'authorizer_user_ids.*' => 'integer|exists:users,id',
            'service_point_supervisors' => 'required|array',
            'service_point_supervisors.*' => 'array|min:1|max:4',
            'service_point_supervisors.*.*' => 'integer|exists:users,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $businessId = (int) $validated['business_id'];
        $approverIds = collect($validated['approver_user_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $authorizerIds = collect($validated['authorizer_user_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $defaultSupervisorId = $validated['default_supervisor_user_id'] ? (int) $validated['default_supervisor_user_id'] : null;

        $servicePointAssignments = [];
        foreach ($request->input('service_point_supervisors', []) as $servicePointId => $userIds) {
            $servicePointAssignments[(int) $servicePointId] = collect($userIds)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->toArray();
        }

        $businessServicePointIds = ServicePoint::where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        $this->assertUsersBelongToBusiness(
            $businessId,
            array_filter(array_merge(
                $approverIds->all(),
                $authorizerIds->all(),
                $defaultSupervisorId ? [$defaultSupervisorId] : [],
                collect($servicePointAssignments)->flatten()->all()
            ))
        );

        if ($approverIds->intersect($authorizerIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'authorizer_user_ids' => 'Approvers and authorizers must be different people.',
            ]);
        }

        $businessServicePointIds = ServicePoint::where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        $this->assertServicePointsBelongToBusiness($businessId, array_keys($servicePointAssignments));

        foreach ($businessServicePointIds as $servicePointId) {
            $desired = $servicePointAssignments[$servicePointId] ?? [];
            if (empty($desired)) {
                throw ValidationException::withMessages([
                    'service_point_supervisors' => 'Please select at least one supervisor for every service point.',
                ]);
            }
        }

        $isActive = $request->boolean('is_active', true);

        DB::beginTransaction();
        try {
            $workflow = CreditNoteWorkflow::create([
                'business_id' => $businessId,
                'default_supervisor_user_id' => $defaultSupervisorId,
                'finance_user_id' => $authorizerIds->first(),
                'ceo_user_id' => $approverIds->first(),
                'is_active' => $isActive,
            ]);

            $workflow->syncAuthorizers($authorizerIds->all());
            $workflow->syncApprovers($approverIds->all());

            [$updated, $unchanged] = $this->syncServicePointSupervisors($businessId, $servicePointAssignments);

            DB::commit();

            return redirect()->route('credit-note-workflows.index')
                ->with('success', 'Credit note workflow created and assignments saved.')
                ->with('service_point_summary', compact('updated', 'unchanged'));
        } catch (\Throwable $e) {
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
        $creditNoteWorkflow->load(['business', 'defaultSupervisor', 'finance', 'ceo', 'approvers', 'authorizers']);
        
        // Get service points for the workflow's business
        $servicePoints = ServicePoint::where('business_id', $creditNoteWorkflow->business_id)
            ->orderBy('name')
            ->get();

        // Get existing service point supervisors
        $servicePointSupervisors = ServicePointSupervisor::where('business_id', $creditNoteWorkflow->business_id)
            ->whereNull('deleted_at')
            ->with('supervisor')
            ->get()
            ->groupBy('service_point_id');

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
        $allServicePoints = ServicePoint::where('business_id', '!=', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'business_id']);

        $servicePointAssignments = ServicePointSupervisor::where('business_id', $creditNoteWorkflow->business_id)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('service_point_id')
            ->map(fn ($group) => $group->pluck('supervisor_user_id')->unique()->values())
            ->toArray();

        $existingApproverIds = $creditNoteWorkflow->approvers->pluck('id')->all();
        $existingAuthorizerIds = $creditNoteWorkflow->authorizers->pluck('id')->all();

        return view('settings.credit-note-workflows.edit', [
            'creditNoteWorkflow' => $creditNoteWorkflow,
            'businesses' => $businesses,
            'allUsers' => $allUsers,
            'allServicePoints' => $allServicePoints,
            'servicePointAssignments' => $servicePointAssignments,
            'existingApproverIds' => $existingApproverIds,
            'existingAuthorizerIds' => $existingAuthorizerIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CreditNoteWorkflow $creditNoteWorkflow)
    {
        if (!in_array('Edit Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'Access denied. You do not have permission to edit credit note workflows.');
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id|unique:credit_note_workflows,business_id,' . $creditNoteWorkflow->id,
            'default_supervisor_user_id' => 'nullable|exists:users,id',
            'approver_user_ids' => 'required|array|min:1|max:3',
            'approver_user_ids.*' => 'integer|exists:users,id',
            'authorizer_user_ids' => 'required|array|min:1|max:3',
            'authorizer_user_ids.*' => 'integer|exists:users,id',
            'service_point_supervisors' => 'required|array',
            'service_point_supervisors.*' => 'array|min:1|max:4',
            'service_point_supervisors.*.*' => 'integer|exists:users,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $businessId = (int) $validated['business_id'];
        $approverIds = collect($validated['approver_user_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $authorizerIds = collect($validated['authorizer_user_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $defaultSupervisorId = $validated['default_supervisor_user_id'] ? (int) $validated['default_supervisor_user_id'] : null;

        $servicePointAssignments = [];
        foreach ($request->input('service_point_supervisors', []) as $servicePointId => $userIds) {
            $servicePointAssignments[(int) $servicePointId] = collect($userIds)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->toArray();
        }

        $this->assertUsersBelongToBusiness(
            $businessId,
            array_filter(array_merge(
                $approverIds->all(),
                $authorizerIds->all(),
                $defaultSupervisorId ? [$defaultSupervisorId] : [],
                collect($servicePointAssignments)->flatten()->all()
            ))
        );

        if ($approverIds->intersect($authorizerIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'authorizer_user_ids' => 'Approvers and authorizers must be different people.',
            ]);
        }

        $businessServicePointIds = ServicePoint::where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        $this->assertServicePointsBelongToBusiness($businessId, array_keys($servicePointAssignments));

        foreach ($businessServicePointIds as $servicePointId) {
            $desired = $servicePointAssignments[$servicePointId] ?? [];
            if (empty($desired)) {
                throw ValidationException::withMessages([
                    'service_point_supervisors' => 'Please select at least one supervisor for every service point.',
                ]);
            }
        }

        $isActive = $request->boolean('is_active', $creditNoteWorkflow->is_active);

        DB::beginTransaction();
        try {
            $creditNoteWorkflow->update([
                'business_id' => $businessId,
                'default_supervisor_user_id' => $defaultSupervisorId,
                'finance_user_id' => $authorizerIds->first(),
                'ceo_user_id' => $approverIds->first(),
                'is_active' => $isActive,
            ]);

            $creditNoteWorkflow->syncAuthorizers($authorizerIds->all());
            $creditNoteWorkflow->syncApprovers($approverIds->all());

            [$updated, $unchanged] = $this->syncServicePointSupervisors($businessId, $servicePointAssignments);

            DB::commit();

            return redirect()->route('credit-note-workflows.index')
                ->with('success', 'Credit note workflow updated and assignments saved.')
                ->with('service_point_summary', compact('updated', 'unchanged'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update workflow: ' . $e->getMessage());
        }
    }

    protected function assertUsersBelongToBusiness(int $businessId, array $userIds): void
    {
        $userIds = collect($userIds)->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $validIds = User::whereIn('id', $userIds)
            ->where('business_id', $businessId)
            ->pluck('id')
            ->toBase();

        if ($validIds->count() !== $userIds->count()) {
            throw ValidationException::withMessages([
                'staff_selection' => 'All selected staff members must belong to the chosen business.',
            ]);
        }
    }

    protected function assertServicePointsBelongToBusiness(int $businessId, array $servicePointIds): void
    {
        $servicePointIds = collect($servicePointIds)->filter()->unique()->values();

        if ($servicePointIds->isEmpty()) {
            return;
        }

        $validIds = ServicePoint::whereIn('id', $servicePointIds)
            ->where('business_id', $businessId)
            ->pluck('id')
            ->toBase();

        if ($validIds->count() !== $servicePointIds->count()) {
            throw ValidationException::withMessages([
                'service_point_supervisors' => 'One or more service points are invalid for the selected business.',
            ]);
        }
    }

    /**
     * @return array{updated:int, unchanged:int}
     */
    protected function syncServicePointSupervisors(int $businessId, array $assignments): array
    {
        $servicePointIds = ServicePoint::where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        if (empty($servicePointIds)) {
            return [0, 0];
        }

        $existingAssignments = ServicePointSupervisor::withTrashed()
            ->where('business_id', $businessId)
            ->whereIn('service_point_id', $servicePointIds)
            ->get()
            ->groupBy('service_point_id');

        $updated = 0;
        $unchanged = 0;

        foreach ($servicePointIds as $servicePointId) {
            $desiredIds = collect($assignments[$servicePointId] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $currentAssignments = $existingAssignments->get($servicePointId, collect());
            $currentIds = $currentAssignments
                ->whereNull('deleted_at')
                ->pluck('supervisor_user_id')
                ->sort()
                ->values()
                ->toArray();

            if ($currentIds === $desiredIds) {
                $unchanged++;
                continue;
            }

            foreach ($desiredIds as $userId) {
                $assignment = $currentAssignments->firstWhere('supervisor_user_id', $userId);
                if ($assignment) {
                    if ($assignment->trashed()) {
                        $assignment->restore();
                    }
                } else {
                    ServicePointSupervisor::create([
                        'service_point_id' => $servicePointId,
                        'supervisor_user_id' => $userId,
                        'business_id' => $businessId,
                    ]);
                }
            }

            foreach ($currentAssignments as $assignment) {
                if (!in_array($assignment->supervisor_user_id, $desiredIds, true)) {
                    $assignment->delete();
                }
            }

            $updated++;
        }

        return [$updated, $unchanged];
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
