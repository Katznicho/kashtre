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
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;
use App\Models\Branch;

class GoodsServicesTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $businessId;
    protected $business;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
        $this->business = Business::find($businessId);
    }

    public function headings(): array
    {
        $baseHeaders = [
            'Name',
            'Code (Auto-generated if empty)',
            'Type (service/good)',
            'Description',
            'Group Name',
            'Subgroup Name',
            'Department Name',
            'Unit of Measure',
            'Default Price',
            'Hospital Share (%)',
            'Contractor Username',
            'Other Names',
        ];

        // Get branches for this business to create dynamic columns
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        // Add pricing columns for each branch
        foreach ($branches as $branch) {
            $baseHeaders[] = $branch->name . ' - Price';
        }
        
        // Add service point columns for each branch
        foreach ($branches as $branch) {
            $baseHeaders[] = $branch->name . ' - Service Point';
        }

        return $baseHeaders;
    }

    public function array(): array
    {
        // Return empty array - clean template with no sample data
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
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
        $groups = Group::where('business_id', $this->businessId)->pluck('name')->toArray();
        $departments = Department::where('business_id', $this->businessId)->pluck('name')->toArray();
        $units = ItemUnit::where('business_id', $this->businessId)->pluck('name')->toArray();
        $servicePoints = ServicePoint::where('business_id', $this->businessId)->pluck('name')->toArray();
        $contractors = ContractorProfile::with('user')->where('business_id', $this->businessId)->get()->pluck('user.name')->filter()->toArray();
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        // Set a default range for data validation (rows 2-1000)
        $startRow = 2;
        $endRow = 1000;
        
        // Column mappings for the new structure
        $columns = [
            'C' => 'Type',        // Type (service/good)
            'E' => 'Group',       // Group Name
            'F' => 'Subgroup',    // Subgroup Name
            'G' => 'Department',  // Department Name
            'H' => 'Unit',        // Unit of Measure
            'I' => 'DefaultPrice', // Default Price
            'J' => 'HospitalShare', // Hospital Share (%)
            'K' => 'Contractor',  // Contractor Username
        ];
        
        // Add data validation for Type column (C)
        $this->addValidationToColumn($worksheet, 'C', $startRow, $endRow, 'service,good', 'Type', false);
        
        // Add data validation for Group Name column (E)
        if (!empty($groups)) {
            $this->addValidationToColumn($worksheet, 'E', $startRow, $endRow, implode(',', $groups), 'Group');
        }
        
        // Add data validation for Subgroup Name column (F)
        if (!empty($groups)) {
            $this->addValidationToColumn($worksheet, 'F', $startRow, $endRow, implode(',', $groups), 'Subgroup');
        }
        
        // Add data validation for Department Name column (G)
        if (!empty($departments)) {
            $this->addValidationToColumn($worksheet, 'G', $startRow, $endRow, implode(',', $departments), 'Department');
        }
        
        // Add data validation for Unit of Measure column (H)
        if (!empty($units)) {
            $this->addValidationToColumn($worksheet, 'H', $startRow, $endRow, implode(',', $units), 'Unit');
        }
        
        // Add data validation for Contractor column (K)
        if (!empty($contractors)) {
            $this->addValidationToColumn($worksheet, 'K', $startRow, $endRow, implode(',', $contractors), 'Contractor');
        }
        
        // Add data validation for service point columns (dynamic) - filter by branch
        $headers = $this->headings();
        
        foreach ($headers as $index => $header) {
            if (strpos($header, ' - Service Point') !== false) {
                // Extract branch name from header
                $branchName = str_replace(' - Service Point', '', $header);
                
                // Get service points for this specific branch
                $branch = $branches->where('name', $branchName)->first();
                if ($branch) {
                    $branchServicePoints = ServicePoint::where('business_id', $this->businessId)
                        ->where('branch_id', $branch->id)
                        ->pluck('name')
                        ->toArray();
                    
                    if (!empty($branchServicePoints)) {
                        $columnLetter = $this->getColumnLetter($index + 1);
                        $this->addValidationToColumn($worksheet, $columnLetter, $startRow, $endRow, implode(',', $branchServicePoints), 'Service Point');
                    }
                }
            }
        }
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
            $validation->setFormula1('"' . $formula . '"');
            $validation->setErrorTitle('Invalid ' . $type);
            $validation->setError('Please select a valid ' . strtolower($type));
            $validation->setPromptTitle('Select ' . $type);
            $validation->setPrompt('Choose a ' . strtolower($type) . ' from the dropdown');
        }
    }
    
    private function getColumnLetter($columnIndex)
    {
        $columnLetter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $columnLetter = chr(65 + ($columnIndex % 26)) . $columnLetter;
            $columnIndex = intval($columnIndex / 26);
        }
        return $columnLetter;
    }
} 