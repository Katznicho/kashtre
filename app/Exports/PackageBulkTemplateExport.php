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

        // Add constituent items columns (simplified - only name and quantity)
        for ($i = 1; $i <= 10; $i++) {
            $baseHeaders[] = "Constituent Item {$i} Name";
            $baseHeaders[] = "Constituent Item {$i} Quantity";
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
        
        // Add data validation for Constituent Items (simplified - 10 items, every 2nd column for names)
        // Calculate starting column dynamically (after core fields + branch prices)
        $startColumnIndex = 6 + $branches->count(); // 6 core fields + number of branch price columns
        
        Log::info("=== PACKAGE/BULK TEMPLATE DEBUG ===");
        Log::info("Branches count: " . $branches->count());
        Log::info("Start column index: " . $startColumnIndex);
        Log::info("Available items count: " . count($items));
        
        $columnLetters = [];
        for ($i = 0; $i < 10; $i++) {
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
} 