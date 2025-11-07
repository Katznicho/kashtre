<?php

namespace App\Exports;

use App\Models\CreditNoteWorkflow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RefundWorkflowSupervisorsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected CreditNoteWorkflow $workflow;

    protected Collection $servicePoints;

    protected Collection $supervisors;

    /**
     * @param  Collection  $servicePoints  Collection of ServicePoint models.
     * @param  Collection  $supervisors    Collection of User models.
     */
    public function __construct(CreditNoteWorkflow $workflow, Collection $servicePoints, Collection $supervisors)
    {
        $this->workflow = $workflow;
        $this->servicePoints = $servicePoints;
        $this->supervisors = $supervisors;
    }

    public function headings(): array
    {
        $headings = [
            'Service Point ID',
            'Service Point Name',
            'Service Point Description',
        ];

        foreach ($this->supervisors as $supervisor) {
            $headings[] = sprintf('Supervisor ID %d - %s (%s)', $supervisor->id, $supervisor->name, $supervisor->email);
        }

        $headings[] = 'Use Default Supervisor';
        $headings[] = 'Notes';

        return $headings;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->servicePoints as $servicePoint) {
            $row = [
                $servicePoint->id,
                $servicePoint->name ?? 'N/A',
                $servicePoint->description ?? '',
            ];

            foreach ($this->supervisors as $supervisor) {
                $row[] = ''; // Placeholder for marking assignment (e.g., enter 1)
            }

            $row[] = ''; // Use Default Supervisor column
            $row[] = 'Enter 1 to assign the supervisor for this service point. Leave all columns blank to keep existing assignments.';

            $rows[] = $row;
        }

        return $rows;
    }
}


