<?php

namespace App\Exports;

use App\Models\Business;
use App\Models\CreditNoteWorkflow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RefundWorkflowSupervisorsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected Business $business;

    protected ?CreditNoteWorkflow $workflow;

    protected Collection $servicePoints;

    protected Collection $supervisors;

    protected array $approverIds = [];

    protected array $authorizerIds = [];

    protected Collection $servicePointAssignments;

    /**
     * @param  Business  $business        Business the workflow belongs to.
     * @param  CreditNoteWorkflow|null $workflow Existing workflow instance, if any.
     * @param  Collection  $servicePoints Collection of ServicePoint models.
     * @param  Collection  $supervisors   Collection of User models.
     * @param  Collection  $servicePointAssignments Grouped supervisor assignments keyed by service_point_id.
     */
    public function __construct(Business $business, ?CreditNoteWorkflow $workflow, Collection $servicePoints, Collection $supervisors, Collection $servicePointAssignments)
    {
        $this->business = $business;
        $this->workflow = $workflow?->loadMissing(['approvers', 'authorizers']);
        $this->servicePoints = $servicePoints;
        $this->supervisors = $supervisors;
        $this->servicePointAssignments = $servicePointAssignments->map(function ($group) {
            return collect($group)->pluck('supervisor_user_id')->unique()->values()->toArray();
        });

        if ($this->workflow) {
            $this->approverIds = $this->workflow->approvers->pluck('id')->unique()->values()->toArray();
            $this->authorizerIds = $this->workflow->authorizers->pluck('id')->unique()->values()->toArray();
        }
    }

    public function headings(): array
    {
        $headings = ['Role / Service Point'];

        foreach ($this->supervisors as $supervisor) {
            $headings[] = sprintf('%s (%s)', $supervisor->name, $supervisor->email);
        }

        return $headings;
    }

    public function array(): array
    {
        $rows = [];

        // Approver row
        $rows[] = $this->buildRoleRow(
            'Approver',
            $this->approverIds
        );

        // Spacer row
        $rows[] = $this->buildSpacerRow();

        // Authorizer row
        $rows[] = $this->buildRoleRow(
            'Authorizer',
            $this->authorizerIds
        );

        // Empty spacer row
        $rows[] = $this->buildSpacerRow();

        // Title row for service points
        $rows[] = ['Service Points'];

        // Service point rows
        foreach ($this->servicePoints as $servicePoint) {
            $label = $servicePoint->name ?? 'Unnamed Service Point';
            $assignedSupervisorIds = $this->servicePointAssignments->get($servicePoint->id, []);
            $rows[] = $this->buildRoleRow($label, $assignedSupervisorIds);
        }

        return $rows;
    }

    protected function buildRoleRow(string $label, array $selectedUserIds = []): array
    {
        $row = [$label];

        foreach ($this->supervisors as $supervisor) {
            $row[] = in_array($supervisor->id, $selectedUserIds, true) ? 'Y' : '';
        }

        return $row;
    }

    protected function buildSpacerRow(): array
    {
        $row = [''];

        foreach ($this->supervisors as $supervisor) {
            $row[] = '';
        }

        return $row;
    }
}


