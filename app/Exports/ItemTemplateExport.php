<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ItemTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Example data
        return [
            ['Sample Item 1', 10.99],
            ['Sample Item 2', 25.50],
            ['Sample Item 3', 5.99],
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Price',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
            'A' => [
                'alignment' => ['horizontal' => 'left']
            ],
            'B' => [
                'alignment' => ['horizontal' => 'right'],
                'numberFormat' => ['formatCode' => '#,##0.00']
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 15,
        ];
    }
}
