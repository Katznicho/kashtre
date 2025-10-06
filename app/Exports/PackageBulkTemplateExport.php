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
        // Create the exact layout from the screenshot
        $baseHeaders = [
            'Package/Bulk Item Name', // A1
            'Code (Auto-generated if empty)', // A2
            'Type (package/bulk)', // A3
            'Description', // A4
            'Default Price', // A5
            'Validity Period (Days) - Required for packages', // A6
            'Other Names', // A7
            'BRANCH1 - Price', // A8
            'BRANCH2 - Price', // A9
            '', // A10 - Empty for spacing
            'Item1', // B1 - Constituent items section
            'Item2', // C1
            'Item3', // D1
            '', // E1 - Empty for spacing
            'Qty1', // F1 - Quantity section
            'Qty2', // G1
            'Qty3', // H1
        ];

        return $baseHeaders;
    }

    public function array(): array
    {
        // Create the exact layout from the screenshot
        $sampleData = [];
        
        // Get available items for the constituents list
        $availableItems = Item::where('business_id', $this->businessId)
            ->whereIn('type', ['service', 'good'])
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        // Create the main template structure
        $templateData = [
            // Row 1: Headers
            $this->headings(),
            
            // Row 2: Sample package data
            [
                'Sample Package', // Package/Bulk Item Name
                '', // Code (auto-generated)
                'package', // Type
                'Sample package description', // Description
                '1000', // Default Price
                '30', // Validity Period
                'Sample package', // Other Names
                '1200', // BRANCH1 - Price
                '1100', // BRANCH2 - Price
                '', // Empty for spacing
                '', // Item1
                '', // Item2
                '', // Item3
                '', // Empty for spacing
                '', // Qty1
                '', // Qty2
                '', // Qty3
            ],
            
            // Row 3: Empty row for spacing
            array_fill(0, count($this->headings()), ''),
            
            // Row 4: Constituents header
            [
                'Constituents(full items list where type is good/service)',
                '', '', '', '', '', '', '', '', // Empty cells
                '', '', '', '', '', '', '' // Empty cells for constituent section
            ],
            
            // Row 5: Empty row
            array_fill(0, count($this->headings()), ''),
        ];
        
        // Add available items as reference (starting from row 6)
        foreach ($availableItems as $item) {
            $itemRow = array_fill(0, count($this->headings()), '');
            $itemRow[0] = $item; // Show item name in first column
            $templateData[] = $itemRow;
        }
        
        return $templateData;
    }



    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Header row styling (row 1)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6'] // Purple for packages/bulk
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
            
            // Sample data row (row 2)
            2 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'] // Light gray for sample
                ]
            ],
            
            // Constituents header (row 4)
            4 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'] // Gray for constituents header
                ]
            ],
        ];
        
        // Style the available items list (starting from row 6)
        $availableItemsCount = Item::where('business_id', $this->businessId)
            ->whereIn('type', ['service', 'good'])
            ->count();
            
        for ($i = 6; $i <= 6 + $availableItemsCount; $i++) {
            $styles[$i] = [
                'font' => ['size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'] // Very light gray for reference items
                ]
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
        
        // Add data validation for Constituent Items (Item1, Item2, Item3 columns)
        // Based on the new layout: Item1=column 11, Item2=column 12, Item3=column 13
        $itemColumns = ['K', 'L', 'M']; // Item1, Item2, Item3 columns
        
        Log::info("=== PACKAGE/BULK TEMPLATE DEBUG ===");
        Log::info("Available items count: " . count($items));
        Log::info("Item columns: " . implode(', ', $itemColumns));
        
        foreach ($itemColumns as $column) {
            for ($row = $startRow; $row <= $endRow; $row++) {
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
        // Add instructions in a separate section (starting from row 1, column I)
        $instructions = [
            'I1' => '📋 TEMPLATE INSTRUCTIONS:',
            'I2' => '',
            'I3' => '1️⃣ PACKAGE/BULK ITEM:',
            'I4' => '   • Fill in the main item details',
            'I5' => '   • Leave Code empty for auto-generation',
            'I6' => '   • For packages, specify validity period',
            'I7' => '',
            'I8' => '2️⃣ CONSTITUENT ITEMS:',
            'I9' => '   • Use dropdowns in Item1, Item2, Item3',
            'I10' => '   • Enter quantities in Qty1, Qty2, Qty3',
            'I11' => '   • At least one item with quantity required',
            'I12' => '   • Available items listed below',
        ];
        
        foreach ($instructions as $cell => $text) {
            $worksheet->setCellValue($cell, $text);
            if (strpos($text, '📋') !== false || strpos($text, '1️⃣') !== false || strpos($text, '2️⃣') !== false) {
                $worksheet->getStyle($cell)->getFont()->setBold(true);
                $worksheet->getStyle($cell)->getFont()->setSize(11);
            } else {
                $worksheet->getStyle($cell)->getFont()->setSize(10);
            }
        }
    }
} 