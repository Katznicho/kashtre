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
        $data = $sheet->toArray(null, true, true, true);
        
        // Find the row with names (should be row 1, index 0)
        $namesRow = null;
        $typesRow = null;
        $pricesRow = null;
        $descriptionsRow = null;
        $validityRow = null;
        $otherNamesRow = null;
        $branchPriceRows = [];
        $constituentsHeaderRow = null;
        
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
            } elseif (isset($row[0]) && strpos($row[0], ' - Price') !== false) {
                // This is a branch price row
                $branchPriceRows[] = [
                    'index' => $index,
                    'branch_name' => str_replace(' - Price', '', $row[0])
                ];
            } elseif (isset($row[0]) && strpos($row[0], 'Constituents') !== false) {
                // This is the constituents header row
                $constituentsHeaderRow = $index;
            }
        }
        
        Log::info("Found rows - Names: " . ($namesRow + 1) . ", Types: " . ($typesRow + 1) . ", Prices: " . ($pricesRow + 1));
        
        // Process each item column (Item1, Item2, Item3, etc.)
        for ($i = 1; $i <= 25; $i++) {
            $itemName = $data[$namesRow][$i] ?? null;
            $itemType = $data[$typesRow][$i] ?? null;
            $itemPrice = $data[$pricesRow][$i] ?? null;
            
            // Debug: Log the values we're checking
            Log::info("DEBUG Item{$i}: Name='{$itemName}', Type='{$itemType}', Price='{$itemPrice}'");
            Log::info("DEBUG Item{$i}: Name type=" . gettype($itemName) . ", Type type=" . gettype($itemType));
            Log::info("DEBUG Item{$i}: Name length=" . strlen($itemName) . ", Type length=" . strlen($itemType));
            Log::info("DEBUG Item{$i}: Name ord=" . ord($itemName) . ", Type ord=" . ord($itemType));
            Log::info("DEBUG Item{$i}: is_numeric(name)=" . (is_numeric($itemName) ? 'true' : 'false'));
            Log::info("DEBUG Item{$i}: is_numeric(type)=" . (is_numeric($itemType) ? 'true' : 'false'));
            
            // Skip if no name or if name looks like template instructions
            if (empty($itemName) || 
                is_numeric($itemName) || 
                strpos($itemName, 'Default:') !== false || 
                strpos($itemName, 'Add columns') !== false ||
                strpos($itemName, 'Type dropdowns') !== false) {
                Log::info("Skipping Item{$i}: Empty or template instruction - '{$itemName}'");
                continue;
            }
            
            // Skip if type is numeric (template artifacts)
            if (is_numeric($itemType)) {
                Log::info("Skipping Item{$i}: Type is numeric (template artifact) - '{$itemType}'");
                continue;
            }
            
            // Skip if both type and price are empty
            if (empty($itemType) && empty($itemPrice)) {
                Log::info("Skipping Item{$i}: No type or price data - '{$itemName}'");
                continue;
            }
            $itemDescription = $data[$descriptionsRow][$i] ?? null;
            $itemValidity = $data[$validityRow][$i] ?? null;
            $itemOtherNames = $data[$otherNamesRow][$i] ?? null;
            
            Log::info("Processing Item{$i}: {$itemName} (Type: {$itemType}, Price: {$itemPrice})");
            
            // Create the item
            $item = $this->createPackageBulkItem($itemName, $itemType, $itemPrice, $itemDescription, $itemValidity, $itemOtherNames, $i);
            
            // If item was created successfully, capture branch prices and constituent items
            if ($item) {
                $this->captureBranchPrices($data, $branchPriceRows, $item, $i);
                $this->captureConstituentItems($data, $constituentsHeaderRow, $item, $i);
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
            
            return $item;

        } catch (\Exception $e) {
            Log::error("Error creating item {$name}: " . $e->getMessage());
            $this->errors[] = "Item{$itemNumber}: Error creating item - " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }

    private function captureBranchPrices($data, $branchPriceRows, $item, $itemNumber)
    {
        Log::info("=== CAPTURING BRANCH PRICES FOR ITEM {$itemNumber} ===");
        Log::info("Item: {$item->name} (ID: {$item->id})");
        Log::info("Found " . count($branchPriceRows) . " branch price rows");
        
        foreach ($branchPriceRows as $branchPriceRow) {
            $branchName = $branchPriceRow['branch_name'];
            $rowIndex = $branchPriceRow['index'];
            $columnIndex = $itemNumber; // Item1 = column 1, Item2 = column 2, etc.
            
            $branchPrice = $data[$rowIndex][$columnIndex] ?? null;
            
            if (!empty($branchPrice) && is_numeric($branchPrice)) {
                // Find the branch by name
                $branch = $this->branches->where('name', $branchName)->first();
                
                if ($branch) {
                    $this->branchPrices[] = [
                        'business_id' => $this->businessId,
                        'item_id' => $item->id,
                        'branch_id' => $branch->id,
                        'price' => (float) $branchPrice,
                        'item_code' => $item->code
                    ];
                    
                    Log::info("Captured branch price for {$branchName}: {$branchPrice}");
                } else {
                    Log::warning("Branch not found: {$branchName}");
                }
            } else {
                Log::info("No price found for {$branchName} in column {$columnIndex}");
            }
        }
    }
    
    private function captureConstituentItems($data, $constituentsHeaderRow, $item, $itemNumber)
    {
        if (!$constituentsHeaderRow) {
            Log::info("No constituents header row found for Item{$itemNumber}");
            return;
        }
        
        Log::info("=== CAPTURING CONSTITUENT ITEMS FOR ITEM {$itemNumber} ===");
        Log::info("Item: {$item->name} (ID: {$item->id}, Type: {$item->type})");
        Log::info("Constituents header row: " . ($constituentsHeaderRow + 1));
        
        // Process constituent items starting from the row after the header
        $constituentRow = $constituentsHeaderRow + 1;
        $constituentCount = 0;
        
        // Look for constituent items in the next 30 rows
        for ($row = $constituentRow; $row < $constituentRow + 30; $row++) {
            if (!isset($data[$row]) || !isset($data[$row][0])) {
                continue;
            }
            
            $constituentName = $data[$row][0];
            $quantity = $data[$row][$itemNumber] ?? null; // Get quantity from the item's column
            
            // Skip if no constituent name or quantity
            if (empty($constituentName) || empty($quantity) || !is_numeric($quantity)) {
                continue;
            }
            
            // Find the constituent item in the database
            $constituentItem = Item::where('business_id', $this->businessId)
                ->where('name', $constituentName)
                ->whereIn('type', ['service', 'good'])
                ->first();
            
            if ($constituentItem) {
                $constituentCount++;
                
                if ($item->type === 'package') {
                    $this->pendingIncludedItems[] = [
                        'type' => 'package',
                        'business_id' => $this->businessId,
                        'package_item_id' => $item->id,
                        'included_item_id' => $constituentItem->id,
                        'max_quantity' => (int) $quantity,
                        'item_name' => $item->name,
                        'constituent_name' => $constituentItem->name
                    ];
                } elseif ($item->type === 'bulk') {
            $this->pendingIncludedItems[] = [
                        'type' => 'bulk',
                        'business_id' => $this->businessId,
                        'bulk_item_id' => $item->id,
                        'included_item_id' => $constituentItem->id,
                        'fixed_quantity' => (int) $quantity,
                        'item_name' => $item->name,
                        'constituent_name' => $constituentItem->name
                    ];
                }
                
                Log::info("Found constituent: {$constituentName} (Qty: {$quantity})");
            } else {
                Log::warning("Constituent item not found in database: {$constituentName}");
            }
        }
        
        Log::info("Captured {$constituentCount} constituent items for Item{$itemNumber}");
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
                    // Only include fields that PackageItem model expects
                    $packageData = [
                        'business_id' => $includedItemData['business_id'],
                        'package_item_id' => $includedItemData['package_item_id'],
                        'included_item_id' => $includedItemData['included_item_id'],
                        'max_quantity' => $includedItemData['max_quantity']
                    ];
                    PackageItem::create($packageData);
                } elseif ($includedItemData['type'] === 'bulk') {
                    // Only include fields that BulkItem model expects
                    $bulkData = [
                        'business_id' => $includedItemData['business_id'],
                        'bulk_item_id' => $includedItemData['bulk_item_id'],
                        'included_item_id' => $includedItemData['included_item_id'],
                        'fixed_quantity' => $includedItemData['fixed_quantity']
                    ];
                    BulkItem::create($bulkData);
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