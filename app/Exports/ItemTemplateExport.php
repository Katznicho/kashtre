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
            'Contractor Kashtre Account Number',
            'Other Names',
            'Pricing Type (default/custom)',
            'Branch Name',
            'Branch Price',
        ];
    }

    public function array(): array
    {
        // Generic template with sample data
        return [
            [
                'Your Business Name', // Business Name - User fills this
                'Sample Item 1', // Name
                'ITEM001', // Code
                'service', // Type
                'Sample service description', // Description
                'Sample Group', // Group Name
                'Sample Subgroup', // Subgroup Name
                'Sample Department', // Department Name
                'Piece', // Unit of Measure
                'Sample Service Point', // Service Point Name
                '100.00', // Default Price
                '100', // Hospital Share (100% = no contractor needed)
                '', // Contractor Kashtre Account Number (not needed when hospital share is 100%)
                'Alternative name 1', // Other Names
                'default', // Pricing Type
                '', // Branch Name
                '', // Branch Price
            ],
            [
                'Your Business Name', // Business Name - User fills this
                'Sample Item 2', // Name
                'ITEM002', // Code
                'good', // Type
                'Sample good description', // Description
                'Sample Group', // Group Name
                '', // Subgroup Name
                'Sample Department', // Department Name
                'Piece', // Unit of Measure
                'Sample Service Point', // Service Point Name
                '50.00', // Default Price
                '80', // Hospital Share (80% = contractor gets 20%)
                'KC1234567890', // Contractor Kashtre Account Number (REQUIRED when hospital share < 100%)
                'Alternative name 2', // Other Names
                'custom', // Pricing Type
                'Sample Branch', // Branch Name
                '45.00', // Branch Price
            ],
            [
                'Your Business Name', // Business Name - User fills this
                'Sample Item 3', // Name
                'ITEM003', // Code
                'package', // Type
                'Sample package description', // Description
                '', // Group Name
                '', // Subgroup Name
                '', // Department Name
                '', // Unit of Measure
                '', // Service Point Name
                '200.00', // Default Price
                '70', // Hospital Share (70% = contractor gets 30%)
                'KC9876543210', // Contractor Kashtre Account Number (REQUIRED when hospital share < 100%)
                '', // Other Names
                'default', // Pricing Type
                '', // Branch Name
                '', // Branch Price
            ],
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