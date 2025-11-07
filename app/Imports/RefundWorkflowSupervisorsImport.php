<?php

namespace App\Imports;

use App\Models\CreditNoteWorkflow;
use App\Models\ServicePoint;
use App\Models\ServicePointSupervisor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\HeadingRowFormatter;

HeadingRowFormatter::default('snake');

class RefundWorkflowSupervisorsImport implements ToCollection, WithHeadingRow
{
    protected CreditNoteWorkflow $workflow;

    protected array $summary = [
        'updated' => 0,
        'unchanged' => 0,
        'errors' => [],
    ];

    public function __construct(CreditNoteWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function collection(Collection $rows)
    {
        $businessId = $this->workflow->business_id;

        $validServicePointIds = ServicePoint::where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        $validSupervisorIds = User::where('business_id', $businessId)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for heading row

            $servicePointId = (int) ($row['service_point_id'] ?? 0);

            if (!$servicePointId || !in_array($servicePointId, $validServicePointIds, true)) {
                $this->summary['errors'][] = "Row {$rowNumber}: Invalid or missing service_point_id.";
                continue;
            }

            $selectedSupervisorId = $this->resolveSupervisorFromRow($row, $validSupervisorIds, $rowNumber);

            try {
                $this->applySupervisorAssignment($servicePointId, $selectedSupervisorId);
                $selectedSupervisorId === null
                    ? $this->summary['unchanged']++
                    : $this->summary['updated']++;
            } catch (\Throwable $e) {
                Log::error('Failed to assign supervisor from bulk import', [
                    'workflow_id' => $this->workflow->id,
                    'service_point_id' => $servicePointId,
                    'error' => $e->getMessage(),
                ]);
                $this->summary['errors'][] = "Row {$rowNumber}: {$e->getMessage()}";
            }
        }
    }

    protected function resolveSupervisorFromRow(Collection $row, array $validSupervisorIds, int $rowNumber): ?int
    {
        $selectedSupervisorId = null;

        foreach ($row as $heading => $value) {
            if ($heading === 'service_point_id' || $heading === 'service_point_name' || $heading === 'service_point_description' || $heading === 'notes') {
                continue;
            }

            if ($heading === 'use_default_supervisor') {
                if ($this->isTruthy($value)) {
                    return $this->workflow->default_supervisor_user_id ?: null;
                }
                continue;
            }

            if (!Str::startsWith($heading, 'supervisor_id_')) {
                continue;
            }

            if (!$this->isTruthy($value)) {
                continue;
            }

            if ($selectedSupervisorId !== null) {
                $this->summary['errors'][] = "Row {$rowNumber}: Multiple supervisors selected. Only the first selection will be applied.";
                continue;
            }

            $extractedId = $this->extractSupervisorIdFromHeading($heading);

            if ($extractedId && in_array($extractedId, $validSupervisorIds, true)) {
                $selectedSupervisorId = $extractedId;
            } else {
                $this->summary['errors'][] = "Row {$rowNumber}: Supervisor referenced in column '{$heading}' is not valid for this business.";
            }
        }

        return $selectedSupervisorId;
    }

    protected function extractSupervisorIdFromHeading(string $heading): ?int
    {
        if (preg_match('/supervisor_id_(\d+)/', $heading, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function applySupervisorAssignment(int $servicePointId, ?int $supervisorUserId): void
    {
        $existingSupervisor = ServicePointSupervisor::where('service_point_id', $servicePointId)
            ->where('business_id', $this->workflow->business_id)
            ->first();

        if ($supervisorUserId) {
            if ($existingSupervisor) {
                $existingSupervisor->update(['supervisor_user_id' => $supervisorUserId]);
            } else {
                ServicePointSupervisor::create([
                    'service_point_id' => $servicePointId,
                    'supervisor_user_id' => $supervisorUserId,
                    'business_id' => $this->workflow->business_id,
                ]);
            }
        } elseif ($existingSupervisor) {
            // No supervisor selected and default supervisor not available - remove specific assignment
            $existingSupervisor->delete();
        }
    }

    protected function isTruthy($value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'yes', 'y', 'true', 'assigned'], true);
    }

    public function getSummary(): array
    {
        $message = 'Bulk assignment completed.';

        if ($this->summary['updated'] > 0) {
            $message .= ' Updated ' . $this->summary['updated'] . ' service point(s).';
        }

        $message .= ' ' . $this->summary['unchanged'] . ' service point(s) unchanged.';

        if (!empty($this->summary['errors'])) {
            $message .= ' Some rows could not be processed. Please review the error list below.';
        }

        return array_merge($this->summary, [
            'message' => trim($message),
        ]);
    }
}


