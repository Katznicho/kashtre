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
            ['Sample Business', 'user@example.com', 'Sample Bank', 'Sample Account', '1234567890', '1000.00', 'KC1234567890', 'Sample Qualifications'],
            ['Sample Business', 'user2@example.com', 'Sample Bank', 'Sample Account 2', '0987654321', '500.00', '', 'Sample Qualifications 2'],
            ['Sample Business', 'user3@example.com', 'Sample Bank', 'Sample Account 3', '1122334455', '750.00', 'KC9876543210', 'Sample Qualifications 3'],
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