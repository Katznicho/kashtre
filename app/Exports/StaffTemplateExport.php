<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use App\Models\ServicePoint;

class StaffTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
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
            'Gender (male/female/other)',
            'Qualification Name',
            'Title Name',
            'Department Name',
            'Section Name',
            'Status (active/inactive/suspended)',
            'Service Point Name',
            'Allowed Branch Name',
            'Is Contractor (Yes/No)',
            'Bank Name',
            'Account Name',
            'Account Number',
        ];
    }

    public function array(): array
    {
        // Return empty array - template with just headers and dropdowns
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->addDataValidation($event);
            },
        ];
    }

    private function addDataValidation(AfterSheet $event)
    {
        $worksheet = $event->sheet->getDelegate();
        
        // Get the data for dropdowns
        $qualifications = Qualification::where('business_id', $this->businessId)->pluck('name')->toArray();
        $titles = Title::where('business_id', $this->businessId)->pluck('name')->toArray();
        $departments = Department::where('business_id', $this->businessId)->pluck('name')->toArray();
        $sections = Section::where('business_id', $this->businessId)->pluck('name')->toArray();
        $servicePoints = ServicePoint::where('business_id', $this->businessId)->pluck('name')->toArray();
        $branches = Branch::where('business_id', $this->businessId)->pluck('name')->toArray();
        
        // Set a default range for data validation (rows 2-1000)
        $startRow = 2;
        $endRow = 1000;
        
        // Column G - Gender dropdown
        $this->addValidationToColumn($worksheet, 'G', $startRow, $endRow, '"male,female,other"', 'Gender', false);
        
        // Column H - Qualification dropdown
        if (!empty($qualifications)) {
            $this->addValidationToColumn($worksheet, 'H', $startRow, $endRow, '"' . implode(',', $qualifications) . '"', 'Qualification', false);
        }
        
        // Column I - Title dropdown
        if (!empty($titles)) {
            $this->addValidationToColumn($worksheet, 'I', $startRow, $endRow, '"' . implode(',', $titles) . '"', 'Title', false);
        }
        
        // Column J - Department dropdown
        if (!empty($departments)) {
            $this->addValidationToColumn($worksheet, 'J', $startRow, $endRow, '"' . implode(',', $departments) . '"', 'Department', false);
        }
        
        // Column K - Section dropdown
        if (!empty($sections)) {
            $this->addValidationToColumn($worksheet, 'K', $startRow, $endRow, '"' . implode(',', $sections) . '"', 'Section', false);
        }
        
        // Column L - Status dropdown
        $this->addValidationToColumn($worksheet, 'L', $startRow, $endRow, '"active,inactive,suspended"', 'Status', false);
        
        // Column M - Service Point dropdown
        if (!empty($servicePoints)) {
            $this->addValidationToColumn($worksheet, 'M', $startRow, $endRow, '"' . implode(',', $servicePoints) . '"', 'Service Point', true);
        }
        
        // Column N - Allowed Branch dropdown
        if (!empty($branches)) {
            $this->addValidationToColumn($worksheet, 'N', $startRow, $endRow, '"' . implode(',', $branches) . '"', 'Allowed Branch', true);
        }
        
        // Column O - Is Contractor dropdown
        $this->addValidationToColumn($worksheet, 'O', $startRow, $endRow, '"Yes,No"', 'Is Contractor', false);
    }
    
    private function addValidationToColumn($worksheet, $column, $startRow, $endRow, $formula, $type, $allowBlank = true)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell($column . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank($allowBlank);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($formula);
            $validation->setErrorTitle('Invalid ' . $type);
            $validation->setError('Please select a valid ' . strtolower($type));
            $validation->setPromptTitle('Select ' . $type);
            $validation->setPrompt('Choose a ' . strtolower($type) . ' from the dropdown');
        }
    }
} 