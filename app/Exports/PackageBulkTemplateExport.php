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
use Illuminate\Support\Facades\Log;

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
        // Get branches for dynamic columns
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        $headings = [
            'name',
            'code_auto_generated_if_empty',
            'type_packagebulk',
            'description',
            'default_price',
            'validity_period_days_required_for_packages',
            'other_names'
        ];
        
        // Add branch price columns
        foreach ($branches as $branch) {
            $headings[] = strtolower(str_replace(' ', '_', $branch->name)) . '_price';
        }
        
        // Add constituent item columns (up to 10 items)
        for ($i = 1; $i <= 10; $i++) {
            $headings[] = "constituent_item_{$i}_name";
            $headings[] = "constituent_item_{$i}_quantity";
        }
        
        return $headings;
    }
    
    public function array(): array
    {
        // Return empty array - template will be populated by user
        return [];
    }
    
    /**
     * Convert column index to Excel column letter (A, B, C, ..., AA, AB, etc.)
     */
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



    public function styles(Worksheet $sheet)
    {
        // Style the first row (headers)
        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'] // Gray for headers
                ]
            ],
        ];
        
        // Calculate constituents header row number
        $branches = Branch::where('business_id', $this->businessId)->count();
        $constituentsHeaderRow = 7 + $branches + 2; // 7 base fields + branches + 2 empty rows
        
        // Style constituents header row
        $styles[$constituentsHeaderRow] = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'] // Gray for constituents header
            ]
        ];
        
        // Style the available items list (blue text)
        $availableItemsCount = Item::where('business_id', $this->businessId)
            ->whereIn('type', ['service', 'good'])
            ->count();
            
        for ($i = $constituentsHeaderRow + 1; $i <= $constituentsHeaderRow + $availableItemsCount; $i++) {
            $styles[$i] = [
                'font' => ['size' => 10, 'color' => ['rgb' => '60A5FA']], // Blue text like in screenshot
            ];
        }
        
        return $styles;
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
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        // Set a default range for data validation (rows 2-1000)
        $startRow = 2;
        $endRow = 1000;
        
        // Add data validation for Type column (C)
        $validation = $worksheet->getCell('C2')->getDataValidation();
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
        
        // Add data validation for Constituent Items
        // Add dropdowns to constituent item name columns
        $constituentColumns = [];
        for ($i = 1; $i <= 10; $i++) {
            $constituentColumns[] = $this->getColumnLetter(7 + $branches->count() + ($i - 1) * 2);
        }
        
        Log::info("=== PACKAGE/BULK TEMPLATE DEBUG ===");
        Log::info("Available items count: " . count($items));
        Log::info("Branches count: " . $branches->count());
        Log::info("Constituent columns: " . implode(', ', $constituentColumns));
        Log::info("Template supports up to 10 constituent items");
        
        // Add dropdowns to constituent item name columns
        foreach ($constituentColumns as $column) {
            $validation = $worksheet->getCell($column . '2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . implode(',', $items) . '"');
            $validation->setErrorTitle('Invalid Constituent Item');
            $validation->setError('Please select a valid constituent item (service or good)');
            $validation->setPromptTitle('Select Constituent Item');
            $validation->setPrompt('Choose a constituent item from the dropdown');
        }
        
        // Add clear instructions for expanding template
        $worksheet->setCellValue('P1', 'TEMPLATE SUPPORTS UP TO 25 ITEMS');
        $worksheet->setCellValue('P2', 'Default: Item1-Item15 (columns B-P)');
        $worksheet->setCellValue('P3', 'To add more:');
        $worksheet->setCellValue('P4', '1. Add columns Q, R, S, T, U...');
        $worksheet->setCellValue('P5', '2. Label as Item16, Item17, Item18...');
        $worksheet->setCellValue('P6', '3. Type dropdowns auto-added');
        $worksheet->setCellValue('P7', '4. Add Qty columns in constituents');
        $worksheet->setCellValue('P8', '5. Copy pattern from Item1');
        
        // Style the instructions
        $worksheet->getStyle('P1')->getFont()->setBold(true);
        $worksheet->getStyle('P1')->getFont()->setSize(11);
        $worksheet->getStyle('P1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0066CC'));
        for ($i = 2; $i <= 8; $i++) {
            $worksheet->getStyle('P' . $i)->getFont()->setSize(9);
        }
        
        // Instructions removed as per user request
    }

    /**
     * Convert column index to Excel column letter (A, B, C, ..., AA, AB, etc.)
     */
    private function getExcelColumnLetter($columnIndex)
    {
        $columnLetter = '';
        while ($columnIndex >= 0) {
            $columnLetter = chr(65 + ($columnIndex % 26)) . $columnLetter;
            $columnIndex = intval($columnIndex / 26) - 1;
        }
        return $columnLetter;
    }
    
    /**
     * Add helpful instructions to the worksheet
     */
    private function addInstructions($worksheet)
    {
        // Add simple instructions at the top
        $worksheet->setCellValue('E1', 'INSTRUCTIONS:');
        $worksheet->setCellValue('E2', '1. Type package/bulk names in columns B, C, D');
        $worksheet->setCellValue('E3', '2. Use Type dropdown to select package/bulk');
        $worksheet->setCellValue('E4', '3. Scroll to Constituents section below');
        $worksheet->setCellValue('E5', '4. Select constituent items from Column A dropdown');
        $worksheet->setCellValue('E6', '5. Enter quantities in Qty columns (B, C, D)');
        
        $worksheet->getStyle('E1')->getFont()->setBold(true);
    }
} 