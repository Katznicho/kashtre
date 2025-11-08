<?php

namespace App\Imports;

use App\Models\Business;
use App\Models\CreditNoteWorkflow;
use App\Models\ServicePoint;
use App\Models\ServicePointSupervisor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class RefundWorkflowSupervisorsImport implements ToCollection
{
    protected Business $business;

    protected CreditNoteWorkflow $workflow;

    protected bool $workflowCreated;

    protected array $summary = [
        'updated' => 0,
        'unchanged' => 0,
        'errors' => [],
        'workflow_status' => null,
    ];

    protected bool $hasBlockingErrors = false;

    public function __construct(Business $business, CreditNoteWorkflow $workflow)
    {
        $this->business = $business;
        $this->workflow = $workflow;
        $this->workflow->business_id = $business->id;
        $this->workflowCreated = !$workflow->exists;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->summary['errors'][] = 'The uploaded template is empty.';
            return;
        }

        $businessUsers = User::where('business_id', $this->business->id)
            ->where('status', 'active')
            ->get(['id', 'email', 'name']);

        if ($businessUsers->isEmpty()) {
            $this->recordError('No active staff members are available for this business.');
            throw new \RuntimeException('No active staff members are available for this business.');
        }

        $headerRow = $rows->shift();
        $columnUsers = $this->mapHeaderToUserIds($headerRow, $businessUsers);

        if (empty($columnUsers)) {
            $this->recordError('No staff columns were detected in the template.');
            throw new \RuntimeException('No staff columns were detected in the template.');
        }

        $validServicePoints = ServicePoint::where('business_id', $this->business->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($validServicePoints->isEmpty()) {
            $this->recordError('No service points are configured for this business.');
            throw new \RuntimeException('No service points are configured for this business.');
        }

        // Approver row (row number 2 in the spreadsheet)
        $approverRow = $rows->shift();
        $approverIds = $this->resolveSelectionsFromRow(
            $approverRow,
            $columnUsers,
            'Approver',
            1,
            3,
            2
        );

        // Spacer row between Approver and Authorizer
        $rows->shift();

        // Authorizer row (row number 3 in the spreadsheet)
        $authorizerRow = $rows->shift();
        $authorizerIds = $this->resolveSelectionsFromRow(
            $authorizerRow,
            $columnUsers,
            'Authorizer',
            1,
            3,
            4
        );

        // Spacer row before the service point title
        $rows->shift();

        // Service Points title row
        $rows->shift();

        $overlap = collect($approverIds)->intersect($authorizerIds);
        if ($overlap->isNotEmpty()) {
            $this->recordError('Approvers and authorizers must be different people.');
            throw new \RuntimeException('Approvers and authorizers must be different people.');
        }

        $servicePointAssignments = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 6; // header + roles + spacer + title
            $label = trim((string) ($row[0] ?? ''));

            if ($label === '') {
                continue;
            }

            $servicePoint = $validServicePoints->firstWhere('name', $label);
            if (! $servicePoint) {
                $this->recordError("Row {$rowNumber}: Service point '{$label}' is not recognized for this business.");
                continue;
            }

            $selectedSupervisorIds = $this->resolveSelectionsFromRow($row, $columnUsers, 'Service Point', 1, 4, $rowNumber);

            if (empty($selectedSupervisorIds)) {
                continue;
            }

            $servicePointAssignments[$servicePoint->id] = $selectedSupervisorIds;
        }

        foreach ($validServicePoints as $servicePoint) {
            if (empty($servicePointAssignments[$servicePoint->id] ?? [])) {
                $this->recordError("Service point '{$servicePoint->name}' must have at least one supervisor selected.");
            }
        }

        if ($this->hasBlockingErrors || ! empty($this->summary['errors'])) {
            $message = $this->summary['errors'][0] ?? 'Import aborted due to template errors.';
            throw new \RuntimeException($message);
        }

        $this->workflow->default_supervisor_user_id = null;
        $this->workflow->is_active = true;
        $this->workflow->save();

        $this->workflowCreated = $this->workflowCreated || $this->workflow->wasRecentlyCreated;

        if (! empty($approverIds)) {
            $this->workflow->syncApprovers($approverIds);
        }

        if (! empty($authorizerIds)) {
            $this->workflow->syncAuthorizers($authorizerIds);
        }

        $updated = 0;
        $unchanged = 0;

        foreach ($servicePointAssignments as $servicePointId => $supervisorIds) {
            try {
                $changed = $this->applySupervisorAssignments($servicePointId, $supervisorIds);
                $changed ? $updated++ : $unchanged++;
            } catch (\Throwable $e) {
                Log::error('Failed to assign supervisor from bulk import', [
                    'business_id' => $this->business->id,
                    'service_point_id' => $servicePointId,
                    'error' => $e->getMessage(),
                ]);
                $this->recordError("Service Point {$servicePointId}: {$e->getMessage()}");
                throw new \RuntimeException("Service Point {$servicePointId}: {$e->getMessage()}");
            }
        }

        $this->summary['updated'] = $updated;
        $this->summary['unchanged'] = $unchanged;
        $this->summary['workflow_status'] = $this->workflow->is_active ? 'Active' : 'Inactive';
    }

    protected function mapHeaderToUserIds($headerRow, Collection $businessUsers): array
    {
        $columnUsers = [];

        if ($headerRow instanceof Collection) {
            $headerRow = $headerRow->toArray();
        }

        foreach ($headerRow as $index => $cell) {
            if ($index === 0) {
                continue;
            }

            $value = trim((string) $cell);
            if ($value === '') {
                continue;
            }

            if (preg_match('/\(([^)]+)\)\s*$/', $value, $matches)) {
                $email = strtolower(trim($matches[1]));
                $user = $businessUsers->first(function ($staff) use ($email) {
                    return strtolower($staff->email) === $email;
                });

                if ($user) {
                    $columnUsers[$index] = (int) $user->id;
                } else {
                    $this->recordError("Column '{$value}' does not match any active staff email.");
                }
            }
        }

        return $columnUsers;
    }

    protected function resolveSelectionsFromRow($row, array $columnUsers, string $context, int $min, int $max, ?int $rowNumber = null): array
    {
        if ($row instanceof Collection) {
            $row = $row->toArray();
        }

        if ($row === null) {
            $row = [];
        }

        $label = trim((string) ($row[0] ?? ''));

        if ($label === '') {
            $this->recordError(($rowNumber ? "Row {$rowNumber}: " : '') . "{$context} row is missing.");
            return [];
        }

        $selectedIds = [];

        foreach ($columnUsers as $index => $userId) {
            $value = $row[$index] ?? null;

            if (! $this->isTruthy($value)) {
                continue;
            }

            if (! in_array($userId, $selectedIds, true)) {
                $selectedIds[] = $userId;
            }
        }

        $count = count($selectedIds);

        if ($count < $min) {
            $message = ($rowNumber ? "Row {$rowNumber}: " : '') . "{$context} requires at least {$min} staff member(s) marked with 'Y'.";
            $this->recordError($message);
            return [];
        }

        if ($count > $max) {
            $message = ($rowNumber ? "Row {$rowNumber}: " : '') . "{$context} can have at most {$max} staff member(s) marked with 'Y'.";
            $this->recordError($message);
            return [];
        }

        return $selectedIds;
    }

    protected function applySupervisorAssignments(int $servicePointId, array $supervisorUserIds): bool
    {
        $supervisorUserIds = array_values(array_unique($supervisorUserIds));

        $existingAssignments = ServicePointSupervisor::withTrashed()
            ->where('service_point_id', $servicePointId)
            ->where('business_id', $this->business->id)
            ->get();

        $activeAssignments = $existingAssignments->whereNull('deleted_at');
        $currentIds = $activeAssignments->pluck('supervisor_user_id')->sort()->values()->toArray();
        $desiredIds = collect($supervisorUserIds)->sort()->values()->toArray();

        if ($currentIds === $desiredIds) {
            return false;
        }

        foreach ($supervisorUserIds as $userId) {
            $assignment = $existingAssignments->firstWhere('supervisor_user_id', $userId);

            if ($assignment) {
                if ($assignment->trashed()) {
                    $assignment->restore();
                }
            } else {
                ServicePointSupervisor::create([
                    'service_point_id' => $servicePointId,
                    'supervisor_user_id' => $userId,
                    'business_id' => $this->business->id,
                ]);
            }
        }

        foreach ($existingAssignments as $assignment) {
            if (! in_array($assignment->supervisor_user_id, $supervisorUserIds, true)) {
                $assignment->delete();
            }
        }

        return true;
    }

    protected function isTruthy($value): bool
    {
        if ($value === null) {
            return false;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'y', 'yes', 'true'], true);
    }

    public function getSummary(): array
    {
        $message = $this->workflowCreated
            ? 'Workflow created and assignments saved.'
            : 'Workflow updated and assignments saved.';

        if (! empty($this->summary['errors'])) {
            $message .= ' Please review the warnings below.';
        }

        return array_merge($this->summary, [
            'message' => $message,
        ]);
    }

    protected function recordError(string $message, bool $blocking = true): void
    {
        $this->summary['errors'][] = $message;

        if ($blocking) {
            $this->hasBlockingErrors = true;
        }
    }
}

