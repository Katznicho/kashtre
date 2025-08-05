<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Business;
use App\Models\Branch;
use App\Models\BranchItemPrice;
use App\Models\PackageItem;
use App\Models\BulkItem;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
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

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    public function model(array $row)
    {
        try {
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
            
            // Validate validity period for packages
            if (strtolower($typeValue) === 'package') {
                if (empty($row['validity_period_days_required_for_packages']) || !is_numeric($row['validity_period_days_required_for_packages'])) {
                    $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Validity period (days) is required for packages";
                    $this->errorCount++;
                    return null;
                }
            }
            
            // Handle branch pricing if provided
            $branchPrice = null;
            if (!empty($row['branch_name']) && !empty($row['branch_price'])) {
                $branch = Branch::where('business_id', $this->businessId)
                    ->where('name', trim($row['branch_name']))
                    ->first();
                
                if ($branch) {
                    $branchPrice = [
                        'branch_id' => $branch->id,
                        'price' => (float) $row['branch_price']
                    ];
                }
            }
            
            // Handle code - check for duplicates and auto-generate if needed
            $code = !empty($row['code_auto_generated_if_empty']) ? trim($row['code_auto_generated_if_empty']) : null;
            
            // If code is provided, check if it already exists
            if ($code) {
                $existingItem = Item::where('code', $code)
                    ->where('business_id', $this->businessId)
                    ->first();
                
                if ($existingItem) {
                    $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Code '{$code}' already exists for item '{$existingItem->name}'. Please use a different code or leave empty for auto-generation.";
                    $this->errorCount++;
                    return null;
                }
            }
            
            // Look up related entities
            $group = null;
            $subgroup = null;
            $department = null;
            $itemUnit = null;
            $servicePoint = null;
            
            if (!empty($row['group_name'])) {
                $group = Group::where('business_id', $this->businessId)
                    ->where('name', trim($row['group_name']))
                    ->first();
            }
            
            if (!empty($row['subgroup_name'])) {
                $subgroup = Group::where('business_id', $this->businessId)
                    ->where('name', trim($row['subgroup_name']))
                    ->first();
            }
            
            if (!empty($row['department_name'])) {
                $department = Department::where('business_id', $this->businessId)
                    ->where('name', trim($row['department_name']))
                    ->first();
            }
            
            if (!empty($row['unit_of_measure'])) {
                $itemUnit = ItemUnit::where('business_id', $this->businessId)
                    ->where('name', trim($row['unit_of_measure']))
                    ->first();
            }
            
            if (!empty($row['service_point_name'])) {
                $servicePoint = ServicePoint::where('business_id', $this->businessId)
                    ->where('name', trim($row['service_point_name']))
                    ->first();
            }
            
            // Create the item
            $item = new Item([
                'name' => trim($row['name']),
                'code' => $code, // Will be auto-generated if empty
                'type' => strtolower($typeValue),
                'description' => !empty($row['description']) ? trim($row['description']) : null,
                'default_price' => (float) $row['default_price'],
                'validity_days' => strtolower($typeValue) === 'package' ? (int) $row['validity_period_days_required_for_packages'] : null,
                'group_id' => $group ? $group->id : null,
                'subgroup_id' => $subgroup ? $subgroup->id : null,
                'department_id' => $department ? $department->id : null,
                'uom_id' => $itemUnit ? $itemUnit->id : null,
                'service_point_id' => $servicePoint ? $servicePoint->id : null,
                'business_id' => $this->businessId,
            ]);
            
            $this->successCount++;
            
            // Store branch price data for later processing
            if ($branchPrice) {
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
        // Store included items data for later processing
        $includedItemsData = [];
        
        // Process up to 5 included items
        for ($i = 1; $i <= 5; $i++) {
            $itemNameKey = "included_item_{$i}_name";
            $itemCodeKey = "included_item_{$i}_code";
            $quantityKey = "included_item_{$i}_quantity";
            
            if (!empty($row[$itemNameKey]) && !empty($row[$quantityKey])) {
                $includedItemsData[] = [
                    'included_item_name' => trim($row[$itemNameKey]),
                    'quantity' => (int) $row[$quantityKey],
                    'type' => $type
                ];
            }
        }
        
        if (!empty($includedItemsData)) {
            $this->pendingIncludedItems[] = [
                'main_item_name' => $mainItemName,
                'included_items' => $includedItemsData
            ];
        }
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
        // Process pending included items data
        foreach ($this->pendingIncludedItems as $pendingData) {
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
                    // Find the included item by name
                    $includedItem = Item::where('business_id', $this->businessId)
                        ->where('name', $includedItemData['included_item_name'])
                        ->first();
                    
                    if (!$includedItem) {
                        Log::error('Included item not found: ' . $includedItemData['included_item_name']);
                        continue;
                    }
                    
                    if ($includedItemData['type'] === 'package') {
                        PackageItem::create([
                            'package_item_id' => $mainItem->id,
                            'included_item_id' => $includedItem->id,
                            'max_quantity' => $includedItemData['quantity'],
                            'business_id' => $this->businessId
                        ]);
                    } else {
                        BulkItem::create([
                            'bulk_item_id' => $mainItem->id,
                            'included_item_id' => $includedItem->id,
                            'fixed_quantity' => $includedItemData['quantity'],
                            'business_id' => $this->businessId
                        ]);
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