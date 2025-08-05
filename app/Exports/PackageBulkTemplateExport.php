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
use App\Models\Item;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;

class PackageBulkTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
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
            'Type (package/bulk)',
            'Description',
            'Default Price',
            'Validity Period (Days) - Required for packages',
            'Group Name',
            'Subgroup Name',
            'Department Name',
            'Unit of Measure',
            'Service Point Name',
            'Branch Name',
            'Branch Price',
            // Included Items Section
            'Included Item 1 Name',
            'Included Item 1 Code',
            'Included Item 1 Quantity',
            'Included Item 2 Name',
            'Included Item 2 Code', 
            'Included Item 2 Quantity',
            'Included Item 3 Name',
            'Included Item 3 Code',
            'Included Item 3 Quantity',
            'Included Item 4 Name',
            'Included Item 4 Code',
            'Included Item 4 Quantity',
            'Included Item 5 Name',
            'Included Item 5 Code',
            'Included Item 5 Quantity',
        ];
    }

    public function array(): array
    {
        return [
            [
                'name' => 'Basic Health Package',
                'code' => '',
                'type' => 'package',
                'description' => 'Essential health services package',
                'default_price' => 150000,
                'validity_period_days' => 30,
                'group_name' => 'Services',
                'subgroup_name' => 'Services',
                'department_name' => 'OPD',
                'unit_of_measure' => 'Procedure',
                'service_point_name' => 'Consultation MO',
                'branch_name' => '',
                'branch_price' => '',
                'included_item_1_name' => 'Consultation Fee',
                'included_item_1_code' => '',
                'included_item_1_quantity' => 2,
                'included_item_2_name' => 'Laboratory Test - Blood Count',
                'included_item_2_code' => '',
                'included_item_2_quantity' => 1,
                'included_item_3_name' => 'Paracetamol 500mg',
                'included_item_3_code' => '',
                'included_item_3_quantity' => 10,
                'included_item_4_name' => '',
                'included_item_4_code' => '',
                'included_item_4_quantity' => '',
                'included_item_5_name' => '',
                'included_item_5_code' => '',
                'included_item_5_quantity' => '',
            ],
            [
                'name' => 'Surgical Supplies Bundle',
                'code' => 'SURG001',
                'type' => 'bulk',
                'description' => 'Complete surgical supplies package',
                'default_price' => 250000,
                'validity_period_days' => '',
                'group_name' => 'Devices',
                'subgroup_name' => 'Devices',
                'department_name' => 'Surgery',
                'unit_of_measure' => 'Kit',
                'service_point_name' => 'Surgical Procedure',
                'branch_name' => '',
                'branch_price' => '',
                'included_item_1_name' => 'Surgical Gloves',
                'included_item_1_code' => '',
                'included_item_1_quantity' => 50,
                'included_item_2_name' => 'Antibiotics - Amoxicillin',
                'included_item_2_code' => '',
                'included_item_2_quantity' => 20,
                'included_item_3_name' => 'X-Ray Chest',
                'included_item_3_code' => '',
                'included_item_3_quantity' => 1,
                'included_item_4_name' => '',
                'included_item_4_code' => '',
                'included_item_4_quantity' => '',
                'included_item_5_name' => '',
                'included_item_5_code' => '',
                'included_item_5_quantity' => '',
            ],
            [
                'name' => 'Premium Care Package',
                'code' => '',
                'type' => 'package',
                'description' => 'Comprehensive healthcare package',
                'default_price' => 300000,
                'validity_period_days' => 90,
                'group_name' => 'Services',
                'subgroup_name' => 'Services',
                'department_name' => 'OPD',
                'unit_of_measure' => 'Procedure',
                'service_point_name' => 'Consultation Surgeon',
                'branch_name' => '',
                'branch_price' => '',
                'included_item_1_name' => 'Consultation Fee',
                'included_item_1_code' => '',
                'included_item_1_quantity' => 5,
                'included_item_2_name' => 'Ultrasound Scan',
                'included_item_2_code' => '',
                'included_item_2_quantity' => 2,
                'included_item_3_name' => 'Physiotherapy Session',
                'included_item_3_code' => '',
                'included_item_3_quantity' => 3,
                'included_item_4_name' => 'Laboratory Test - Blood Count',
                'included_item_4_code' => '',
                'included_item_4_quantity' => 2,
                'included_item_5_name' => 'X-Ray Chest',
                'included_item_5_code' => '',
                'included_item_5_quantity' => 1,
            ],
            [
                'name' => 'Emergency Kit',
                'code' => 'EMERG001',
                'type' => 'bulk',
                'description' => 'Emergency medical supplies kit',
                'default_price' => 75000,
                'validity_period_days' => '',
                'group_name' => 'Drugs',
                'subgroup_name' => 'Drugs',
                'department_name' => 'OPD',
                'unit_of_measure' => 'Kit',
                'service_point_name' => 'Consultation MO',
                'branch_name' => '',
                'branch_price' => '',
                'included_item_1_name' => 'Paracetamol 500mg',
                'included_item_1_code' => '',
                'included_item_1_quantity' => 30,
                'included_item_2_name' => 'Surgical Gloves',
                'included_item_2_code' => '',
                'included_item_2_quantity' => 20,
                'included_item_3_name' => 'Antibiotics - Amoxicillin',
                'included_item_3_code' => '',
                'included_item_3_quantity' => 15,
                'included_item_4_name' => '',
                'included_item_4_code' => '',
                'included_item_4_quantity' => '',
                'included_item_5_name' => '',
                'included_item_5_code' => '',
                'included_item_5_quantity' => '',
            ],
            [
                'name' => 'Diagnostic Package',
                'code' => '',
                'type' => 'package',
                'description' => 'Complete diagnostic services package',
                'default_price' => 180000,
                'validity_period_days' => 60,
                'group_name' => 'Services',
                'subgroup_name' => 'Services',
                'department_name' => 'OPD',
                'unit_of_measure' => 'Procedure',
                'service_point_name' => 'Consultation MO',
                'branch_name' => '',
                'branch_price' => '',
                'included_item_1_name' => 'Laboratory Test - Blood Count',
                'included_item_1_code' => '',
                'included_item_1_quantity' => 2,
                'included_item_2_name' => 'X-Ray Chest',
                'included_item_2_code' => '',
                'included_item_2_quantity' => 1,
                'included_item_3_name' => 'Ultrasound Scan',
                'included_item_3_code' => '',
                'included_item_3_quantity' => 1,
                'included_item_4_name' => 'Consultation Fee',
                'included_item_4_code' => '',
                'included_item_4_quantity' => 3,
                'included_item_5_name' => '',
                'included_item_5_code' => '',
                'included_item_5_quantity' => '',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6'] // Purple for packages/bulk
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
        $items = Item::where('business_id', $this->businessId)
            ->whereIn('type', ['service', 'good'])
            ->pluck('name')
            ->toArray();
        $branches = Branch::where('business_id', $this->businessId)->pluck('name')->toArray();
        $groups = Group::where('business_id', $this->businessId)->pluck('name')->toArray();
        $departments = Department::where('business_id', $this->businessId)->pluck('name')->toArray();
        $itemUnits = ItemUnit::where('business_id', $this->businessId)->pluck('name')->toArray();
        $servicePoints = ServicePoint::where('business_id', $this->businessId)->pluck('name')->toArray();
        
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
            $validation->setFormula1('"package,bulk"');
            $validation->setErrorTitle('Invalid Type');
            $validation->setError('Please select either "package" or "bulk"');
            $validation->setPromptTitle('Select Type');
            $validation->setPrompt('Choose the item type');
        }
        
        // Add data validation for Group Name column (G)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('G' . $row)->getDataValidation();
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
        
        // Add data validation for Subgroup Name column (H)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('H' . $row)->getDataValidation();
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
        
        // Add data validation for Department Name column (I)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('I' . $row)->getDataValidation();
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
        
        // Add data validation for Unit of Measure column (J)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('J' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $itemUnits) . '"');
            $validation->setErrorTitle('Invalid Unit of Measure');
            $validation->setError('Please select a valid unit of measure');
            $validation->setPromptTitle('Select Unit of Measure');
            $validation->setPrompt('Choose a unit of measure from the dropdown');
        }
        
        // Add data validation for Service Point Name column (K)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('K' . $row)->getDataValidation();
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
        
        // Add data validation for Branch Name column (L)
        for ($row = $startRow; $row <= $endRow; $row++) {
            $validation = $worksheet->getCell('L' . $row)->getDataValidation();
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
        
        // Add data validation for Included Items (columns O, R, U, X, AA)
        $includedItemColumns = ['O', 'R', 'U', 'X', 'AA']; // Item names
        foreach ($includedItemColumns as $column) {
            for ($row = $startRow; $row <= $endRow; $row++) {
                $validation = $worksheet->getCell($column . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"' . implode(',', $items) . '"');
                $validation->setErrorTitle('Invalid Item');
                $validation->setError('Please select a valid item');
                $validation->setPromptTitle('Select Item');
                $validation->setPrompt('Choose an item from the dropdown');
            }
        }
    }
} 