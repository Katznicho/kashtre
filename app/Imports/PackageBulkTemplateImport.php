<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Business;
use App\Models\Branch;
use App\Models\BranchItemPrice;
use App\Models\PackageItem;
use App\Models\BulkItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

class PackageBulkTemplateImport implements ToModel, WithHeadingRow, SkipsOnError, WithEvents
{
    protected $businessId;
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $branchPrices = [];
    protected $includedItems = [];
    protected $pendingIncludedItems = [];
    protected $branches = [];

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
        // Load branches for this business
        $this->branches = Branch::where('business_id', $businessId)->orderBy('name')->get();
        
        Log::info("=== PACKAGE/BULK IMPORT INITIALIZED ===");
        Log::info("Business ID: {$businessId}");
        Log::info("Branches found: " . count($this->branches));
        Log::info("Template supports up to 25 constituent items (Item1-Item25)");
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->processHorizontalTemplate($event->sheet);
            },
        ];
    }
    
    private function processHorizontalTemplate($sheet)
    {
        Log::info("=== PROCESSING HORIZONTAL TEMPLATE ===");
        
        // Get all data from the sheet
        $data = $sheet->toArray();
        
        // Find the row with names (should be row 1, index 0)
        $namesRow = null;
        $typesRow = null;
        $pricesRow = null;
        $descriptionsRow = null;
        $validityRow = null;
        $otherNamesRow = null;
        
        foreach ($data as $index => $row) {
            if (isset($row[0]) && $row[0] === 'Name') {
                $namesRow = $index;
            } elseif (isset($row[0]) && $row[0] === 'Type (package/bulk)') {
                $typesRow = $index;
            } elseif (isset($row[0]) && $row[0] === 'Default Price') {
                $pricesRow = $index;
            } elseif (isset($row[0]) && $row[0] === 'Description') {
                $descriptionsRow = $index;
            } elseif (isset($row[0]) && $row[0] === 'Validity Period (Days) - Required for packages') {
                $validityRow = $index;
            } elseif (isset($row[0]) && $row[0] === 'Other Names') {
                $otherNamesRow = $index;
            }
        }
        
        Log::info("Found rows - Names: " . ($namesRow + 1) . ", Types: " . ($typesRow + 1) . ", Prices: " . ($pricesRow + 1));
        
        // Process each item column (Item1, Item2, Item3, etc.)
        for ($i = 1; $i <= 25; $i++) {
            $itemName = $data[$namesRow][$i] ?? null;
            
            if (!empty($itemName)) {
                $itemType = $data[$typesRow][$i] ?? null;
                $itemPrice = $data[$pricesRow][$i] ?? null;
                $itemDescription = $data[$descriptionsRow][$i] ?? null;
                $itemValidity = $data[$validityRow][$i] ?? null;
                $itemOtherNames = $data[$otherNamesRow][$i] ?? null;
                
                Log::info("Processing Item{$i}: {$itemName} (Type: {$itemType}, Price: {$itemPrice})");
                
                // Create the item
                $this->createPackageBulkItem($itemName, $itemType, $itemPrice, $itemDescription, $itemValidity, $itemOtherNames, $i);
            }
        }
    }
    
    private function createPackageBulkItem($name, $type, $price, $description, $validity, $otherNames, $itemNumber)
    {
        try {
            // Validate required fields
            if (empty($name)) {
                $this->errors[] = "Item{$itemNumber}: Name is required";
                $this->errorCount++;
                return;
            }
            
            if (empty($type)) {
                $this->errors[] = "Item{$itemNumber}: Type is required";
                $this->errorCount++;
                return;
            }
            
            if (!in_array(strtolower($type), ['package', 'bulk'])) {
                $this->errors[] = "Item{$itemNumber}: Type must be 'package' or 'bulk', got '{$type}'";
                $this->errorCount++;
                return;
            }
            
            // Create the item
            $item = new Item();
            $item->name = $name;
            $item->code = $this->generateUniqueCode();
            $item->type = strtolower($type);
            $item->description = $description;
            $item->default_price = $price ?? 0;
            $item->vat_rate = 0.00;
            $item->validity_days = $validity;
            $item->hospital_share = 100;
            $item->other_names = $otherNames;
            $item->business_id = $this->businessId;
            $item->group_id = null;
            $item->subgroup_id = null;
            $item->department_id = null;
            $item->uom_id = null;
            $item->contractor_account_id = null;
            
            $item->save();
            
            Log::info("Successfully created item: {$name} (ID: {$item->id})");
            $this->successCount++;
            
        } catch (\Exception $e) {
            Log::error("Error creating item {$name}: " . $e->getMessage());
            $this->errors[] = "Item{$itemNumber}: Error creating item - " . $e->getMessage();
            $this->errorCount++;
        }
    }
    
    public function model(array $row)
    {
        // For horizontal template, we process everything in the AfterSheet event
        // This method is called for each row, but we handle everything at once
        return null;
    }

    public function onError(\Throwable $e)
    {
        Log::error("Import error: " . $e->getMessage());
        $this->errorCount++;
        $this->errors[] = "Import error: " . $e->getMessage();
    }

    public function createBranchPrices()
    {
        Log::info("=== CREATING BRANCH PRICES ===");
        Log::info("Total branch prices to create: " . count($this->branchPrices));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($this->branchPrices as $branchPriceData) {
            try {
                $branchPrice = BranchItemPrice::create($branchPriceData);
                $successCount++;
                Log::info("Successfully created branch price for item '{$branchPriceData['item_code']}' at branch {$branchPriceData['branch_id']} with price {$branchPriceData['price']}");
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Error creating branch price for item '{$branchPriceData['item_code']}': " . $e->getMessage());
            }
        }
        
        Log::info("=== BRANCH PRICES CREATION COMPLETED ===");
        Log::info("Successfully created: {$successCount} branch prices");
        Log::info("Errors encountered: {$errorCount} branch prices");
        Log::info("Branch prices creation completed");
    }

    public function createIncludedItems()
    {
        Log::info("=== CREATING INCLUDED ITEMS ===");
        Log::info("Pending included items count: " . count($this->pendingIncludedItems));
        
        if (empty($this->pendingIncludedItems)) {
            Log::warning("No pending included items to process");
            return;
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($this->pendingIncludedItems as $includedItemData) {
            try {
                if ($includedItemData['type'] === 'package') {
                    PackageItem::create($includedItemData);
                } elseif ($includedItemData['type'] === 'bulk') {
                    BulkItem::create($includedItemData);
                }
                $successCount++;
                Log::info("Successfully created included item for {$includedItemData['type']} item '{$includedItemData['item_name']}'");
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Error creating included item for {$includedItemData['type']} item '{$includedItemData['item_name']}': " . $e->getMessage());
            }
        }
        
        Log::info("=== INCLUDED ITEMS CREATION COMPLETED ===");
        Log::info("Successfully created: {$successCount} included items");
        Log::info("Errors encountered: {$errorCount} included items");
        Log::info("Included items creation completed");
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function generateUniqueCode()
    {
        $prefix = 'ITM';
        $counter = 1;
        
        do {
            $code = $prefix . str_pad($counter, 6, '0', STR_PAD_LEFT);
            $counter++;
        } while (Item::where('code', $code)->exists());
        
        return $code;
    }

    private function normalizeColumnName($name)
    {
        return strtolower(str_replace([' ', '-', '(', ')'], ['_', '_', '', ''], $name));
    }
}