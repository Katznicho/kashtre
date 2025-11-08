<?php

namespace App\Http\Controllers;

use App\Exports\RefundWorkflowSupervisorsExport;
use App\Imports\RefundWorkflowSupervisorsImport;
use App\Models\Business;
use App\Models\CreditNoteWorkflow;
use App\Models\ServicePoint;
use App\Models\ServicePointSupervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CreditNoteWorkflowBulkUploadController extends Controller
{
    /**
     * Display the bulk upload console where a business can be selected.
     */
    public function index(Request $request)
    {
        $this->ensureUserHasPermission('View Credit Note Workflows');

        $user = Auth::user();

        $businessesQuery = Business::query()->orderBy('name');

        if ($user->business_id && $user->business_id !== 1) {
            $businessesQuery->where('id', $user->business_id);
        } else {
            $businessesQuery->where('id', '!=', 1);
        }

        $businesses = $businessesQuery->get();

        $selectedBusinessId = $request->query('business_id');
        $selectedBusiness = null;
        $servicePoints = collect();
        $businessUsers = collect();
        $existingWorkflow = null;

        if ($selectedBusinessId) {
            $selectedBusiness = $businesses->firstWhere('id', (int) $selectedBusinessId);

            if (!$selectedBusiness) {
                abort(403, 'You do not have access to the selected business.');
            }

            $servicePoints = ServicePoint::where('business_id', $selectedBusiness->id)
                ->orderBy('name')
                ->get(['id', 'name', 'description']);

            $businessUsers = User::where('business_id', $selectedBusiness->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            $existingWorkflow = CreditNoteWorkflow::with(['defaultSupervisor', 'finance', 'ceo', 'approvers', 'authorizers'])
                ->where('business_id', $selectedBusiness->id)
                ->first();
        }

        return view('settings.credit-note-workflows.bulk-upload', [
            'businesses' => $businesses,
            'selectedBusiness' => $selectedBusiness,
            'servicePoints' => $servicePoints,
            'businessUsers' => $businessUsers,
            'existingWorkflow' => $existingWorkflow,
        ]);
    }

    /**
     * Download the bulk template for the selected business.
     */
    public function downloadTemplate(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'supervisor_ids' => 'nullable|array',
            'supervisor_ids.*' => 'integer|exists:users,id',
        ]);

        $business = Business::findOrFail($validated['business_id']);

        $this->authorizeBusiness($business, 'Edit Credit Note Workflows');

        $allowedSupervisorIds = User::where('business_id', $business->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($allowedSupervisorIds)) {
            return redirect()->back()->with('error', 'No active supervisors available for this business.');
        }

        $selectedSupervisorIds = collect($validated['supervisor_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $allowedSupervisorIds, true))
            ->values()
            ->toArray();

        $supervisors = User::whereIn('id', $selectedSupervisorIds ?: $allowedSupervisorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $servicePoints = ServicePoint::where('business_id', $business->id)
            ->orderBy('name')
            ->get();

        if ($servicePoints->isEmpty()) {
            return redirect()->back()->with('error', 'No service points found for this business.');
        }

        $workflow = CreditNoteWorkflow::with(['defaultSupervisor', 'finance', 'ceo', 'approvers', 'authorizers'])
            ->where('business_id', $business->id)
            ->first();

        $servicePointAssignments = ServicePointSupervisor::where('business_id', $business->id)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('service_point_id');

        $filename = 'refund-workflow-bulk-template-' . Str::slug($business->name) . '.xlsx';

        return Excel::download(
            new RefundWorkflowSupervisorsExport($business, $workflow, $servicePoints, $supervisors, $servicePointAssignments),
            $filename
        );
    }

    /**
     * Import workflow configuration and supervisor assignments.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $business = Business::findOrFail($validated['business_id']);

        $this->authorizeBusiness($business, 'Edit Credit Note Workflows');

        try {
            DB::beginTransaction();

            $workflow = CreditNoteWorkflow::firstOrNew(['business_id' => $business->id]);

            $import = new RefundWorkflowSupervisorsImport($business, $workflow);
            Excel::import($import, $request->file('file'));

            $summary = $import->getSummary();

            DB::commit();

            return redirect()
                ->route('credit-note-workflows.bulk-upload.index', ['business_id' => $business->id])
                ->with('success', $summary['message'])
                ->with('import_summary', $summary);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Refund workflow bulk upload failed', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('credit-note-workflows.bulk-upload.index', ['business_id' => $business->id])
                ->with('error', 'Bulk upload failed: ' . $e->getMessage());
        }
    }

    protected function ensureUserHasPermission(string $permission): void
    {
        if (!in_array($permission, Auth::user()->permissions ?? [])) {
            abort(403, 'You do not have permission to access refund workflow bulk uploads.');
        }
    }

    protected function authorizeBusiness(Business $business, string $permission): void
    {
        $this->ensureUserHasPermission($permission);

        if (Auth::user()->business_id !== 1 && Auth::user()->business_id !== $business->id) {
            abort(403, 'You do not have access to this business.');
        }
    }
}


