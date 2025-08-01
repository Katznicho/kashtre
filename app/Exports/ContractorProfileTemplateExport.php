<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContractorProfileTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Business Name',
            'User Email',
            'Bank Name',
            'Account Name',
            'Account Number',
            'Account Balance',
            'Kashtre Account Number',
            'Signing Qualifications',
        ];
    }

    public function array(): array
    {
        // Return sample rows for template
        return [
            ['', '', '', '', '', '0.00', '', ''],
            ['', '', '', '', '', '0.00', '', ''],
            ['', '', '', '', '', '0.00', '', ''],
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