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
        return [
            'Name',
            'Code (Auto-generated if empty)',
            'Type (service/good)',
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
        // Return empty array - no sample data, just headers
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
        $contractors = ContractorProfile::where('business_id', $this->businessId)->pluck('kashtre_account_number')->toArray();
        $branches = Branch::where('business_id', $this->businessId)->pluck('name')->toArray();
        
        // Set a default range for data validation (rows 2-1000)
        $startRow = 2;
        $endRow = 1000;
        
        // Add data validation for Type column (C)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('C' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"service,good"');
            $validation->setErrorTitle('Invalid Type');
            $validation->setError('Please select either "service" or "good"');
            $validation->setPromptTitle('Select Type');
            $validation->setPrompt('Choose the item type');
        }
        
        // Add data validation for Group Name column (E)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('E' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $groups) . '"');
            $validation->setErrorTitle('Invalid Group');
            $validation->setError('Please select a valid group');
            $validation->setPromptTitle('Select Group');
            $validation->setPrompt('Choose a group from the dropdown');
        }
        
        // Add data validation for Subgroup Name column (F)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('F' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $groups) . '"');
            $validation->setErrorTitle('Invalid Subgroup');
            $validation->setError('Please select a valid subgroup');
            $validation->setPromptTitle('Select Subgroup');
            $validation->setPrompt('Choose a subgroup from the dropdown');
        }
        
        // Add data validation for Department Name column (G)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('G' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $departments) . '"');
            $validation->setErrorTitle('Invalid Department');
            $validation->setError('Please select a valid department');
            $validation->setPromptTitle('Select Department');
            $validation->setPrompt('Choose a department from the dropdown');
        }
        
        // Add data validation for Unit of Measure column (H)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('H' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $units) . '"');
            $validation->setErrorTitle('Invalid Unit');
            $validation->setError('Please select a valid unit');
            $validation->setPromptTitle('Select Unit');
            $validation->setPrompt('Choose a unit from the dropdown');
        }
        
        // Add data validation for Service Point Name column (I)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('I' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $servicePoints) . '"');
            $validation->setErrorTitle('Invalid Service Point');
            $validation->setError('Please select a valid service point');
            $validation->setPromptTitle('Select Service Point');
            $validation->setPrompt('Choose a service point from the dropdown');
        }
        
        // Add data validation for Contractor column (L)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('L' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $contractors) . '"');
            $validation->setErrorTitle('Invalid Contractor');
            $validation->setError('Please select a valid contractor account number');
            $validation->setPromptTitle('Select Contractor');
            $validation->setPrompt('Choose a contractor account number from the dropdown');
        }
        
        // Add data validation for Branch Name column (O)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('O' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $branches) . '"');
            $validation->setErrorTitle('Invalid Branch');
            $validation->setError('Please select a valid branch');
            $validation->setPromptTitle('Select Branch');
            $validation->setPrompt('Choose a branch from the dropdown');
        }
        
        // Add data validation for Pricing Type column (N)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('N' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"default,custom"');
            $validation->setErrorTitle('Invalid Pricing Type');
            $validation->setError('Please select either "default" or "custom"');
            $validation->setPromptTitle('Select Pricing Type');
            $validation->setPrompt('Choose the pricing type');
        }
    }
} 