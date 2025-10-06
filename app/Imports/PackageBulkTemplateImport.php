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
use Illuminate\Support\Facades\Log;

class PackageBulkTemplateImport implements ToModel, WithHeadingRow, SkipsOnError
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
    }

    public function model(array $row)
    {
        try {
            $rowNumber = $this->getRowNumber() + 1;
            
            // Only log for first 2 items to reduce noise
            if ($rowNumber <= 2) {
                Log::info("=== PROCESSING PACKAGE/BULK ROW ===");
                Log::info("Row number: " . $rowNumber);
                Log::info("Row data: " . json_encode($row));
            }
            
            // Find the type column - use the correct column name from Laravel Excel
            $typeValue = $row['type_packagebulk'] ?? null;
            
            // Skip completely empty rows (no name, no type)
            if (empty($row['name']) && empty($typeValue)) {
                return null; // Skip silently without error
            }
            
            // Manual validation
            if (empty($row['name'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Name is required";
                $this->errorCount++;
                return null;
            }
            
            if (empty($typeValue)) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Type is required";
                $this->errorCount++;
                return null;
            }
            
            if (!in_array(strtolower($typeValue), ['package', 'bulk'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Type must be 'package' or 'bulk', got '{$typeValue}'";
                $this->errorCount++;
                return null;
            }
            
            if (empty($row['default_price']) || !is_numeric($row['default_price'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Default price is required and must be a number";
                $this->errorCount++;
                return null;
            }
            
            // VAT rate is not applicable for packages/bulk items - set to 0
            $vatRate = 0.00;
            
            // Validate validity period for packages
            if (strtolower($typeValue) === 'package') {
                if (empty($row['validity_period_days_required_for_packages']) || !is_numeric($row['validity_period_days_required_for_packages'])) {
                    $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Validity period (days) is required for packages";
                    $this->errorCount++;
                    return null;
                }
            }
            
            // Handle branch-specific pricing (similar to goods/services template)
            $branchPrices = [];
            foreach ($this->branches as $branch) {
                $branchPriceKey = $this->normalizeColumnName($branch->name . ' - Price');
                if (!empty($row[$branchPriceKey]) && is_numeric($row[$branchPriceKey])) {
                    $branchPrices[] = [
                        'branch_id' => $branch->id,
                        'price' => (float) $row[$branchPriceKey]
                    ];
                }
            }
            
            // Handle code - check for duplicates and auto-generate if needed
            $code = !empty($row['code_auto_generated_if_empty']) ? trim($row['code_auto_generated_if_empty']) : null;
            
            // If code is provided, check if it already exists and generate a new one if it does
            if ($code) {
                $originalCode = $code;
                $counter = 1;
                while (Item::where('code', $code)->where('business_id', $this->businessId)->exists()) {
                    $code = $originalCode . '_' . $counter;
                    $counter++;
                }
                
                if ($code !== $originalCode) {
                    Log::info("Row {$rowNumber}: Code '{$originalCode}' already exists, using '{$code}' instead");
                }
            }
            
            // Get other names if provided
            $otherNames = !empty($row['other_names']) ? trim($row['other_names']) : null;
            
            // Validate that packages/bulk items have at least one constituent item
            if ($rowNumber <= 2) {
                Log::info("=== STARTING CONSTITUENT ITEMS VALIDATION ===");
                Log::info("Item name: " . trim($row['name']));
                Log::info("Item type: " . strtolower($typeValue));
            }
            
            $hasConstituentItems = $this->validateConstituentItems($row);
            
            if (!$hasConstituentItems) {
                $errorMessage = "Row " . ($this->getRowNumber() + 1) . ": Packages and bulk items must have at least one constituent item with quantity";
                if ($rowNumber <= 2) {
                    Log::error("❌ VALIDATION FAILED: " . $errorMessage);
                }
                $this->errors[] = $errorMessage;
                $this->errorCount++;
                return null;
            }
            
            if ($rowNumber <= 2) {
                Log::info("✅ CONSTITUENT ITEMS VALIDATION PASSED - Proceeding with item creation");
            }
            
            // CRITICAL: Validate that all constituent items exist in database
            $constituentItemsExist = $this->validateConstituentItemsExist($row);
            if (!$constituentItemsExist) {
                $errorMessage = "Row " . ($this->getRowNumber() + 1) . ": Constituent items must exist in database before creating package/bulk items. Import constituent items first using goods/services template.";
                if ($rowNumber <= 2) {
                    Log::error("❌ CRITICAL VALIDATION FAILED: " . $errorMessage);
                }
                $this->errors[] = $errorMessage;
                $this->errorCount++;
                return null;
            }
            
            if ($rowNumber <= 2) {
                Log::info("✅ ALL CONSTITUENT ITEMS EXIST IN DATABASE - Proceeding with package/bulk item creation");
            }
            
            // Create the item (simplified for packages/bulk - no groups, departments, etc.)
            $item = new Item([
                'name' => trim($row['name']),
                'code' => $code, // Will be auto-generated if empty
                'type' => strtolower($typeValue),
                'description' => !empty($row['description']) ? trim($row['description']) : null,
                'default_price' => (float) $row['default_price'],
                'vat_rate' => $vatRate,
                'validity_days' => strtolower($typeValue) === 'package' ? (int) $row['validity_period_days_required_for_packages'] : null,
                'hospital_share' => 100, // Packages/bulk always have 100% hospital share
                'other_names' => $otherNames,
                'business_id' => $this->businessId,
                // Package/bulk items don't need these fields
                'group_id' => null,
                'subgroup_id' => null,
                'department_id' => null,
                'uom_id' => null,
                'contractor_account_id' => null,
            ]);
            
            // Save the item to the database
            $item->save();
            
            $this->successCount++;
            if ($rowNumber <= 2) {
                Log::info("✅ SUCCESSFULLY CREATED PACKAGE/BULK ITEM: " . $item->name . " (ID: " . $item->id . ")");
            }
            
            // Store branch prices data for later processing
            foreach ($branchPrices as $branchPrice) {
                $this->branchPrices[] = [
                    'item_id' => $item->id,
                    'branch_id' => $branchPrice['branch_id'],
                    'price' => $branchPrice['price']
                ];
            }
            
            // Store included items data for later processing
            $this->storeIncludedItemsData($row, $item->name, $typeValue);
            
            return $item;

        } catch (\Exception $e) {
            $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }

    private function storeIncludedItemsData($row, $mainItemName, $type)
    {
        // Store constituent items data for later processing (improved logic - up to 10 items)
        $includedItemsData = [];
        
        $rowNumber = $this->getRowNumber() + 1;
        if ($rowNumber <= 2) {
            Log::info("=== STORING INCLUDED ITEMS DATA ===");
            Log::info("Main item: {$mainItemName}, Type: {$type}");
            Log::info("Available row keys: " . implode(', ', array_keys($row)));
        }
        
        // Process up to 3 constituent items (simplified structure)
        for ($i = 1; $i <= 3; $i++) {
            $itemNameKey = $this->normalizeColumnName("item{$i}");
            $quantityKey = $this->normalizeColumnName("qty{$i}");
            
            if ($rowNumber <= 2) {
                Log::info("Looking for keys: '{$itemNameKey}' and '{$quantityKey}'");
                Log::info("Item name value: " . ($row[$itemNameKey] ?? 'NOT FOUND'));
                Log::info("Quantity value: " . ($row[$quantityKey] ?? 'NOT FOUND'));
            }
            
            if (!empty($row[$itemNameKey]) && !empty($row[$quantityKey])) {
                $includedItemsData[] = [
                    'included_item_name' => trim($row[$itemNameKey]),
                    'quantity' => (int) $row[$quantityKey],
                    'type' => $type
                ];
                if ($rowNumber <= 2) {
                    Log::info("Added constituent item: " . trim($row[$itemNameKey]) . " (qty: " . (int) $row[$quantityKey] . ")");
                }
            }
        }
        
        if (!empty($includedItemsData)) {
            $this->pendingIncludedItems[] = [
                'main_item_name' => $mainItemName,
                'included_items' => $includedItemsData
            ];
        }
    }

    /**
     * Validate that packages/bulk items have at least one constituent item with quantity
     */
    private function validateConstituentItems($row)
    {
        $rowNumber = $this->getRowNumber() + 1;
        if ($rowNumber <= 2) {
            Log::info("=== VALIDATING CONSTITUENT ITEMS ===");
            Log::info("Row number: " . $rowNumber);
            Log::info("Available row keys: " . implode(', ', array_keys($row)));
        }
        
        $validConstituentItems = 0;
        $totalChecked = 0;
        
        // Check up to 3 constituent items
        for ($i = 1; $i <= 3; $i++) {
            $itemNameKey = $this->normalizeColumnName("item{$i}");
            $quantityKey = $this->normalizeColumnName("qty{$i}");
            
            if ($rowNumber <= 2) {
                Log::info("Checking constituent item {$i}:");
                Log::info("- Looking for keys: '{$itemNameKey}' and '{$quantityKey}'");
                Log::info("- Item name value: " . ($row[$itemNameKey] ?? 'NOT FOUND'));
                Log::info("- Quantity value: " . ($row[$quantityKey] ?? 'NOT FOUND'));
            }
            
            $totalChecked++;
            
            // If we find at least one constituent item with both name and quantity, it's valid
            if (!empty($row[$itemNameKey]) && !empty($row[$quantityKey])) {
                $validConstituentItems++;
                if ($rowNumber <= 2) {
                    Log::info("✓ Found valid constituent item {$i}: " . trim($row[$itemNameKey]) . " (qty: " . (int) $row[$quantityKey] . ")");
                }
            } else {
                if ($rowNumber <= 2) {
                    Log::info("✗ Constituent item {$i} is empty or missing");
                }
            }
        }
        
        if ($rowNumber <= 2) {
            Log::info("=== CONSTITUENT ITEMS VALIDATION SUMMARY ===");
            Log::info("Total constituent item slots checked: {$totalChecked}");
            Log::info("Valid constituent items found: {$validConstituentItems}");
        }
        
        if ($validConstituentItems > 0) {
            if ($rowNumber <= 2) {
                Log::info("✅ VALIDATION PASSED - Package/bulk item has {$validConstituentItems} constituent item(s)");
            }
            return true;
        } else {
            if ($rowNumber <= 2) {
                Log::warning("❌ VALIDATION FAILED - No constituent items found for package/bulk item");
            }
            return false;
        }
    }

    /**
     * Validate that all constituent items exist in the database
     */
    private function validateConstituentItemsExist($row)
    {
        $rowNumber = $this->getRowNumber() + 1;
        $missingItems = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $itemNameKey = $this->normalizeColumnName("item{$i}");
            $quantityKey = $this->normalizeColumnName("qty{$i}");

            if (!empty($row[$itemNameKey]) && !empty($row[$quantityKey])) {
                $itemName = trim($row[$itemNameKey]);
                
                // Check if this constituent item exists in database
                $existingItem = Item::where('business_id', $this->businessId)
                    ->where('name', $itemName)
                    ->first();
                
                if (!$existingItem) {
                    $missingItems[] = $itemName;
                    if ($rowNumber <= 2) {
                        Log::error("❌ MISSING CONSTITUENT ITEM: " . $itemName);
                    }
                } else {
                    if ($rowNumber <= 2) {
                        Log::info("✅ CONSTITUENT ITEM EXISTS: " . $itemName . " (ID: " . $existingItem->id . ")");
                    }
                }
            }
        }
        
        if (!empty($missingItems)) {
            if ($rowNumber <= 2) {
                Log::error("❌ MISSING CONSTITUENT ITEMS: " . implode(', ', $missingItems));
                Log::error("❌ SOLUTION: Import these items first using goods/services template");
            }
            return false;
        }
        
        if ($rowNumber <= 2) {
            Log::info("✅ ALL CONSTITUENT ITEMS EXIST IN DATABASE");
        }
        return true;
    }

    /**
     * Normalize column name to match Laravel Excel's header normalization
     */
    private function normalizeColumnName($columnName)
    {
        // Convert to lowercase and replace spaces and special characters with underscores
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($columnName)));
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": " . $e->getMessage();
        $this->errorCount++;
    }

    public function createBranchPrices()
    {
        // Create branch prices for imported items
        foreach ($this->branchPrices as $branchPriceData) {
            try {
                BranchItemPrice::create([
                    'item_id' => $branchPriceData['item_id'],
                    'branch_id' => $branchPriceData['branch_id'],
                    'price' => $branchPriceData['price'],
                    'business_id' => $this->businessId
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating branch price: ' . $e->getMessage());
            }
        }
        
        Log::info('Created ' . count($this->branchPrices) . ' branch prices for imported packages/bulk items');
    }

    public function createIncludedItems()
    {
        Log::info("=== CREATING INCLUDED ITEMS ===");
        Log::info("Pending included items count: " . count($this->pendingIncludedItems));
        
        if (empty($this->pendingIncludedItems)) {
            Log::warning("No pending included items to process");
            return;
        }
        
        // Process pending included items data
        foreach ($this->pendingIncludedItems as $pendingData) {
            Log::info("Processing main item: " . $pendingData['main_item_name']);
            Log::info("Included items count: " . count($pendingData['included_items']));
            // Find the main item by name
            $mainItem = Item::where('business_id', $this->businessId)
                ->where('name', $pendingData['main_item_name'])
                ->first();
            
            if (!$mainItem) {
                Log::error('Main item not found: ' . $pendingData['main_item_name']);
                continue;
            }
            
            // Process each included item
            foreach ($pendingData['included_items'] as $includedItemData) {
                try {
                    Log::info("=== LOOKING FOR CONSTITUENT ITEM ===");
                    Log::info("Looking for: " . $includedItemData['included_item_name']);
                    Log::info("Business ID: " . $this->businessId);
                    
                    // Find the included item by name
                    $includedItem = Item::where('business_id', $this->businessId)
                        ->where('name', $includedItemData['included_item_name'])
                        ->first();
                    
                    if (!$includedItem) {
                        Log::error("❌ CONSTITUENT ITEM NOT FOUND: " . $includedItemData['included_item_name']);
                        Log::error("❌ CRITICAL ERROR: Cannot create package/bulk item without constituent items!");
                        Log::error("❌ SOLUTION: Import constituent items first using goods/services template");
                        Log::error("❌ AVAILABLE ITEMS:");
                        $availableItems = Item::where('business_id', $this->businessId)->get(['id', 'name', 'type']);
                        foreach ($availableItems as $item) {
                            Log::error("- ID: {$item->id}, Name: '{$item->name}', Type: {$item->type}");
                        }
                        continue;
                    } else {
                        Log::info("✅ FOUND EXISTING CONSTITUENT ITEM: " . $includedItem->name . " (ID: " . $includedItem->id . ")");
                    }
                    
                    try {
                        if ($includedItemData['type'] === 'package') {
                            $packageItem = PackageItem::create([
                                'package_item_id' => $mainItem->id,
                                'included_item_id' => $includedItem->id,
                                'max_quantity' => $includedItemData['quantity'],
                                'business_id' => $this->businessId
                            ]);
                            Log::info("✅ CREATED PACKAGE RELATIONSHIP: Package ID {$mainItem->id} -> Item ID {$includedItem->id} (Qty: {$includedItemData['quantity']}) - DB ID: {$packageItem->id}");
                        } else {
                            $bulkItem = BulkItem::create([
                                'bulk_item_id' => $mainItem->id,
                                'included_item_id' => $includedItem->id,
                                'fixed_quantity' => $includedItemData['quantity'],
                                'business_id' => $this->businessId
                            ]);
                            Log::info("✅ CREATED BULK RELATIONSHIP: Bulk ID {$mainItem->id} -> Item ID {$includedItem->id} (Qty: {$includedItemData['quantity']}) - DB ID: {$bulkItem->id}");
                        }
                    } catch (\Exception $e) {
                        Log::error("❌ ERROR CREATING RELATIONSHIP: " . $e->getMessage());
                        Log::error("❌ STACK TRACE: " . $e->getTraceAsString());
                        continue;
                    }
                    
                    $this->includedItems[] = [
                        'main_item_id' => $mainItem->id,
                        'included_item_id' => $includedItem->id,
                        'quantity' => $includedItemData['quantity'],
                        'type' => $includedItemData['type']
                    ];
                    
                } catch (\Exception $e) {
                    Log::error('Error creating included item: ' . $e->getMessage());
                }
            }
        }
        
        Log::info('Created ' . count($this->includedItems) . ' included items for packages/bulk items');
        
        // Verify relationships were actually saved to database
        $this->verifyRelationshipsSaved();
    }

    /**
     * Verify that relationships were actually saved to the database
     */
    private function verifyRelationshipsSaved()
    {
        Log::info("=== VERIFYING RELATIONSHIPS SAVED TO DATABASE ===");
        
        // Check package items
        $packageItemsCount = PackageItem::where('business_id', $this->businessId)->count();
        Log::info("Total package items in database: {$packageItemsCount}");
        
        // Check bulk items  
        $bulkItemsCount = BulkItem::where('business_id', $this->businessId)->count();
        Log::info("Total bulk items in database: {$bulkItemsCount}");
        
        // Show recent relationships
        $recentPackages = PackageItem::where('business_id', $this->businessId)
            ->with(['packageItem', 'includedItem'])
            ->latest()
            ->limit(5)
            ->get();
            
        Log::info("Recent package relationships:");
        foreach ($recentPackages as $pkg) {
            Log::info("- Package: {$pkg->packageItem->name} (ID: {$pkg->package_item_id}) -> Item: {$pkg->includedItem->name} (ID: {$pkg->included_item_id}) - Qty: {$pkg->max_quantity}");
        }
        
        $recentBulks = BulkItem::where('business_id', $this->businessId)
            ->with(['bulkItem', 'includedItem'])
            ->latest()
            ->limit(5)
            ->get();
            
        Log::info("Recent bulk relationships:");
        foreach ($recentBulks as $bulk) {
            Log::info("- Bulk: {$bulk->bulkItem->name} (ID: {$bulk->bulk_item_id}) -> Item: {$bulk->includedItem->name} (ID: {$bulk->included_item_id}) - Qty: {$bulk->fixed_quantity}");
        }
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

    private function getRowNumber()
    {
        // This is a simple implementation - in a real scenario you might want to track row numbers more accurately
        return $this->successCount + $this->errorCount;
    }
} 