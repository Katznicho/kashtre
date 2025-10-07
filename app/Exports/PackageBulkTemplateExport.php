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
        // Return empty array - we'll create a custom layout
        return [];
    }

    public function array(): array
    {
        // Create horizontal layout with row headers (like in screenshot)
        // Get branches for dynamic branch price rows
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        // Get available items for the constituents list
        $availableItems = Item::where('business_id', $this->businessId)
            ->whereIn('type', ['service', 'good'])
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        $templateData = [
            // Row 1: Column headers (Item1, Item2, Item3)
            ['Package/Bulk Item Name', 'Item1', 'Item2', 'Item3'],
            
            // Row 2: Code
            ['Code (Auto-generated if empty)', '', '', ''],
            
            // Row 3: Type
            ['Type (package/bulk)', '', '', ''],
            
            // Row 4: Description
            ['Description', '', '', ''],
            
            // Row 5: Default Price
            ['Default Price', '', '', ''],
            
            // Row 6: Validity Period
            ['Validity Period (Days) - Required for packages', '', '', ''],
            
            // Row 7: Other Names
            ['Other Names', '', '', ''],
        ];
        
        // Add branch price rows dynamically
        foreach ($branches as $branch) {
            $templateData[] = [$branch->name . ' - Price', '', '', ''];
        }
        
        // Add empty row for spacing
        $templateData[] = ['', '', '', ''];
        $templateData[] = ['', '', '', ''];
        
        // Add constituents header row
        $templateData[] = ['Constituents(full items list where type is good/service', 'Qty', 'Qty', 'Qty'];
        
        // Add available items as reference
        foreach ($availableItems as $item) {
            $templateData[] = [$item, '', '', ''];
        }
        
        return $templateData;
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
        
        // Add data validation for Type row (row 3, columns B, C, D)
        $typeColumns = ['B', 'C', 'D'];
        foreach ($typeColumns as $column) {
            $validation = $worksheet->getCell($column . '3')->getDataValidation();
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
        
        // Calculate constituents header row
        $constituentsHeaderRow = 7 + $branches->count() + 2;
        
        // Add data validation for Constituent Items (columns B, C, D for Item1, Item2, Item3)
        // Start from the row after constituents header
        $itemColumns = ['B', 'C', 'D']; // Item1, Item2, Item3 columns
        $availableItemsCount = count($items);
        
        Log::info("=== PACKAGE/BULK TEMPLATE DEBUG ===");
        Log::info("Available items count: " . $availableItemsCount);
        Log::info("Constituents header row: " . $constituentsHeaderRow);
        Log::info("Item columns: " . implode(', ', $itemColumns));
        
        foreach ($itemColumns as $column) {
            // Add dropdown validation for all rows from constituents header + 1 to end of available items
            for ($row = $constituentsHeaderRow + 1; $row <= $constituentsHeaderRow + $availableItemsCount; $row++) {
                $validation = $worksheet->getCell($column . $row)->getDataValidation();
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
        }
        
        // Add helpful instructions
        $this->addInstructions($worksheet);
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
        $worksheet->setCellValue('E2', '1. Fill package/bulk details in columns B, C, D');
        $worksheet->setCellValue('E3', '2. Use Type dropdown (package/bulk)');
        $worksheet->setCellValue('E4', '3. Select constituent items from dropdown list below');
        $worksheet->setCellValue('E5', '4. Enter quantities for each constituent item');
        
        $worksheet->getStyle('E1')->getFont()->setBold(true);
    }
} 