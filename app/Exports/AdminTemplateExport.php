<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Surname',
            'First Name',
            'Middle Name',
            'Email',
            'Phone',
            'NIN',
            'Gender (male or female)',
        ];
    }

    public function array(): array
    {
        // Return empty rows for template
        return [
            ['', '', '', '', '', '', 'male'],
            ['', '', '', '', '', '', 'female'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }
} 