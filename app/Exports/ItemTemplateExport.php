<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Business Name',
            'Name',
            'Code',
            'Type (service/good/package/bulk)',
            'Description',
            'Group Name',
            'Subgroup Name',
            'Department Name',
            'Unit of Measure',
            'Service Point Name',
            'Default Price',
            'Hospital Share (%)',
            'Contractor Email',
            'Other Names',
            'Pricing Type (default/custom)',
            'Branch Name',
            'Branch Price',
        ];
    }

    public function array(): array
    {
        // Return sample rows for template
        return [
            ['', '', '', 'service', '', '', '', '', '', '', '0.00', '100', '', '', 'default', '', ''],
            ['', '', '', 'good', '', '', '', '', '', '', '0.00', '80', '', '', 'custom', '', ''],
            ['', '', '', 'package', '', '', '', '', '', '', '0.00', '100', '', '', 'default', '', ''],
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