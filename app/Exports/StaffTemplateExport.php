<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use App\Models\ServicePoint;

class StaffTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected $businessId;
    protected $branchId;
    protected $business;
    protected $branch;

    public function __construct($businessId, $branchId)
    {
        $this->businessId = $businessId;
        $this->branchId = $branchId;
        $this->business = Business::find($businessId);
        $this->branch = Branch::find($branchId);
    }

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
            'Is Contractor (Yes or No)',
            'Bank Name',
            'Account Name',
            'Account Number',
        ];
    }

    public function array(): array
    {
        // Get business-specific data for reference
        $qualifications = Qualification::where('business_id', $this->businessId)->pluck('name')->toArray();
        $titles = Title::where('business_id', $this->businessId)->pluck('name')->toArray();
        $departments = Department::where('business_id', $this->businessId)->pluck('name')->toArray();
        $sections = Section::where('business_id', $this->businessId)->pluck('name')->toArray();
        $servicePoints = ServicePoint::where('business_id', $this->businessId)->pluck('name')->toArray();

        // Return sample data
        return [
            [
                'Sample', 'Staff', 'Name', 'staff@example.com', '1234567890', '1234567890123456', 'male', 'No', '', '', ''
            ],
            [
                'Sample', 'Contractor', 'Name', 'contractor@example.com', '0987654321', '6543210987654321', 'female', 'Yes', 'Sample Bank', 'Sample Account', '1234567890'
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