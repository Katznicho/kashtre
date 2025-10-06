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
        $baseHeaders = [
            'Name',
            'Code (Auto-generated if empty)',
            'Type (package/bulk)',
            'Description',
            'Default Price',
            'Validity Period (Days) - Required for packages',
            'Other Names',
        ];

        // Get branches for this business to create dynamic pricing columns
        $branches = Branch::where('business_id', $this->businessId)->orderBy('name')->get();
        
        // Add pricing columns for each branch
        foreach ($branches as $branch) {
            $baseHeaders[] = $branch->name . ' - Price';
        }

        // Add constituent items columns with clear Item1, Item2, Item3 structure
        for ($i = 1; $i <= 3; $i++) {
            $baseHeaders[] = "Item{$i}";
            $baseHeaders[] = "Qty{$i}";
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
        
        // Add data validation for Constituent Items (simplified - 3 items, every 2nd column for names)
        // Calculate starting column dynamically (after core fields + branch prices)
        $startColumnIndex = 7 + $branches->count(); // 7 core fields + number of branch price columns
        
        Log::info("=== PACKAGE/BULK TEMPLATE DEBUG ===");
        Log::info("Branches count: " . $branches->count());
        Log::info("Start column index: " . $startColumnIndex);
        Log::info("Available items count: " . count($items));
        
        // Log all headers to understand structure
        $headers = $this->headings();
        Log::info("Total headers: " . count($headers));
        for ($i = 0; $i < count($headers); $i++) {
            $columnLetter = $this->getExcelColumnLetter($i);
            Log::info("Header " . $i . " (" . $columnLetter . "): " . $headers[$i]);
        }
        
        $columnLetters = [];
        for ($i = 0; $i < 3; $i++) {
            $columnIndex = $startColumnIndex + ($i * 2); // Every 2nd column (name, quantity, name, quantity...)
            $columnLetter = $this->getExcelColumnLetter($columnIndex);
            $columnLetters[] = $columnLetter;
            Log::info("Constituent Item " . ($i + 1) . " Name column: " . $columnLetter . " (index: " . $columnIndex . ")");
        }
        
        foreach ($columnLetters as $column) {
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
        // Add instructions in row 1 (after headers)
        $instructions = [
            'A1' => 'INSTRUCTIONS:',
            'A2' => '1. Fill in Package/Bulk Item details (Name, Type, Description, Price, etc.)',
            'A3' => '2. For constituent items:',
            'A4' => '   - Use dropdowns in Item1, Item2, Item3 columns to select items',
            'A5' => '   - Enter quantities in Qty1, Qty2, Qty3 columns',
            'A6' => '   - At least one constituent item with quantity is required',
            'A7' => '3. Leave Code empty for auto-generation',
            'A8' => '4. For packages, specify validity period in days',
        ];
        
        foreach ($instructions as $cell => $text) {
            $worksheet->setCellValue($cell, $text);
            $worksheet->getStyle($cell)->getFont()->setBold(true);
            $worksheet->getStyle($cell)->getFont()->setSize(10);
        }
    }
} 