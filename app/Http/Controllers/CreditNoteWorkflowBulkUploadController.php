<?php

namespace App\Http\Controllers;

use App\Exports\RefundWorkflowSupervisorsExport;
use App\Imports\RefundWorkflowSupervisorsImport;
use App\Models\CreditNoteWorkflow;
use App\Models\ServicePoint;
use App\Models\ServicePointSupervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CreditNoteWorkflowBulkUploadController extends Controller
{
    /**
     * Show the bulk upload interface for a refund workflow.
     */
    public function show(CreditNoteWorkflow $creditNoteWorkflow)
    {
        $this->authorizeView($creditNoteWorkflow);

        $creditNoteWorkflow->load(['business']);

        $servicePoints = ServicePoint::where('business_id', $creditNoteWorkflow->business_id)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $businessUsers = User::where('business_id', $creditNoteWorkflow->business_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('settings.credit-note-workflows.bulk-upload', [
            'workflow' => $creditNoteWorkflow,
            'servicePoints' => $servicePoints,
            'businessUsers' => $businessUsers,
        ]);
    }

    /**
     * Download the supervisors template for the selected workflow.
     */
    public function downloadTemplate(Request $request, CreditNoteWorkflow $creditNoteWorkflow)
    {
        $this->authorizeView($creditNoteWorkflow);

        $validated = $request->validate([
            'supervisor_ids' => 'nullable|array',
            'supervisor_ids.*' => 'exists:users,id',
        ]);

        $creditNoteWorkflow->load('business');

        $allowedSupervisorIds = User::where('business_id', $creditNoteWorkflow->business_id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        $selectedSupervisorIds = collect($validated['supervisor_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $allowedSupervisorIds, true))
            ->values()
            ->toArray();

        $supervisors = User::whereIn('id', $selectedSupervisorIds ?: $allowedSupervisorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $servicePoints = ServicePoint::where('business_id', $creditNoteWorkflow->business_id)
            ->orderBy('name')
            ->get();

        if ($servicePoints->isEmpty()) {
            return redirect()->back()->with('error', 'No service points found for this business.');
        }

        if ($supervisors->isEmpty()) {
            return redirect()->back()->with('error', 'No active supervisors available for this business.');
        }

        $filename = 'refund-workflow-supervisors-' . str_replace(' ', '-', strtolower($creditNoteWorkflow->business->name)) . '.xlsx';

        return Excel::download(
            new RefundWorkflowSupervisorsExport($creditNoteWorkflow, $servicePoints, $supervisors),
            $filename
        );
    }

    /**
     * Import supervisor assignments from the uploaded template.
     */
    public function import(Request $request, CreditNoteWorkflow $creditNoteWorkflow)
    {
        $this->authorizeEdit($creditNoteWorkflow);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $import = new RefundWorkflowSupervisorsImport($creditNoteWorkflow);
            Excel::import($import, $request->file('file'));

            $summary = $import->getSummary();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', $summary['message'])
                ->with('import_summary', $summary);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Refund workflow bulk upload failed', [
                'workflow_id' => $creditNoteWorkflow->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Bulk assignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the current user can view the workflow.
     */
    protected function authorizeView(CreditNoteWorkflow $workflow): void
    {
        if (!in_array('View Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'You do not have permission to view refund workflows.');
        }

        if (Auth::user()->business_id !== 1 && Auth::user()->business_id !== $workflow->business_id) {
            abort(403, 'You do not have access to this refund workflow.');
        }
    }

    /**
     * Ensure the current user can edit the workflow.
     */
    protected function authorizeEdit(CreditNoteWorkflow $workflow): void
    {
        if (!in_array('Edit Credit Note Workflows', auth()->user()->permissions ?? [])) {
            abort(403, 'You do not have permission to edit refund workflows.');
        }

        if (Auth::user()->business_id !== 1 && Auth::user()->business_id !== $workflow->business_id) {
            abort(403, 'You do not have access to this refund workflow.');
        }
    }
}


