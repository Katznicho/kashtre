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
use App\Services\ContractorValidationService;

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
            'VAT Rate (%)',
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
                $this->addWorksheetProtection($event);
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
        $contractors = ContractorValidationService::getAvailableContractors($this->businessId);
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
            'J' => 'VATRate',     // VAT Rate (%)
            'K' => 'HospitalShare', // Hospital Share (%)
            'L' => 'Contractor',  // Contractor Username
        ];
        
        // Add data validation for Type column (C)
        $this->addValidationToColumn($worksheet, 'C', $startRow, $endRow, 'service,good', 'Type', false);
        
        // Add data validation for Group Name column (E)
        if (!empty($groups)) {
            $this->addValidationToColumn($worksheet, 'E', $startRow, $endRow, implode(',', $groups), 'Group');
        } else {
            // Add placeholder validation to prevent Excel issues
            $this->addValidationToColumn($worksheet, 'E', $startRow, $endRow, 'No groups available', 'Group');
        }
        
        // Add data validation for Subgroup Name column (F)
        if (!empty($groups)) {
            $this->addValidationToColumn($worksheet, 'F', $startRow, $endRow, implode(',', $groups), 'Subgroup');
        } else {
            $this->addValidationToColumn($worksheet, 'F', $startRow, $endRow, 'No subgroups available', 'Subgroup');
        }
        
        // Add data validation for Department Name column (G)
        if (!empty($departments)) {
            $this->addValidationToColumn($worksheet, 'G', $startRow, $endRow, implode(',', $departments), 'Department');
        } else {
            $this->addValidationToColumn($worksheet, 'G', $startRow, $endRow, 'No departments available', 'Department');
        }
        
        // Add data validation for Unit of Measure column (H)
        if (!empty($units)) {
            $this->addValidationToColumn($worksheet, 'H', $startRow, $endRow, implode(',', $units), 'Unit');
        } else {
            $this->addValidationToColumn($worksheet, 'H', $startRow, $endRow, 'No units available', 'Unit');
        }
        
        // Add data validation for Contractor column (L) - Required when hospital share < 100%
        if (!empty($contractors)) {
            $this->addValidationToColumn($worksheet, 'L', $startRow, $endRow, implode(',', $contractors), 'Contractor');
        } else {
            $this->addValidationToColumn($worksheet, 'L', $startRow, $endRow, 'No contractors available', 'Contractor');
        }
        
        // Add conditional validation for hospital share and contractor relationship
        $this->addConditionalValidation($worksheet, $startRow, $endRow);
        
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
        // Apply validation to a range instead of individual cells for better performance
        $range = $column . $startRow . ':' . $column . $endRow;
        $validation = $worksheet->getCell($column . $startRow)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank($allowBlank);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        
        // Escape the formula properly for Excel
        $validation->setFormula1('"' . str_replace('"', '""', $formula) . '"');
        $validation->setErrorTitle('Invalid ' . $type);
        $validation->setError('Please select a valid ' . strtolower($type));
        $validation->setPromptTitle('Select ' . $type);
        $validation->setPrompt('Choose a ' . strtolower($type) . ' from the dropdown');
        
        // Apply the validation to the entire range
        $worksheet->setDataValidation($range, $validation);
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
    
    /**
     * Add conditional validation for hospital share and contractor relationship
     * This adds custom validation rules to ensure contractor is selected when hospital share < 100%
     */
    private function addConditionalValidation($worksheet, $startRow, $endRow)
    {
        // Add validation for hospital share column (K) - simpler approach
        $hospitalShareRange = 'K' . $startRow . ':' . 'K' . $endRow;
        $validation = $worksheet->getCell('K' . $startRow)->getDataValidation();
        $validation->setType(DataValidation::TYPE_WHOLE);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
        $validation->setFormula1('0');
        $validation->setFormula2('100');
        $validation->setErrorTitle('Invalid Hospital Share');
        $validation->setError('Hospital share must be between 0 and 100');
        $validation->setPromptTitle('Hospital Share');
        $validation->setPrompt('Enter a value between 0 and 100');
        $worksheet->setDataValidation($hospitalShareRange, $validation);
        
        // Remove complex conditional validation for contractor column to avoid Excel issues
        // Instead, we'll rely on the import validation to check the business logic
        // This prevents Excel from showing repair dialogs
    }
    
    /**
     * Add worksheet protection to prevent accidental modification of headers
     */
    private function addWorksheetProtection(AfterSheet $event)
    {
        $worksheet = $event->sheet->getDelegate();
        
        // Protect the header row (row 1)
        $worksheet->getProtection()->setSheet(true);
        $worksheet->getProtection()->setPassword('kashtre2024');
        
        // Allow editing of data rows but protect the header
        $worksheet->getStyle('A1:' . $this->getColumnLetter(count($this->headings())) . '1')
            ->getProtection()
            ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);
    }
} 