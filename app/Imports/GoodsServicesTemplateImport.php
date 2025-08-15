<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Business;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;
use App\Models\Branch;
use App\Models\BranchItemPrice;
use App\Models\BranchServicePoint;
use App\Services\ContractorValidationService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GoodsServicesTemplateImport implements ToModel, WithHeadingRow, SkipsOnError
{
    protected $businessId;
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $branchPrices = [];
    protected $branchServicePoints = [];

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    public function model(array $row)
    {
        try {
            // Find the type column - try different normalized variations
            $typeValue = $row['type_servicegood'] ?? $row['type'] ?? null;
            
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
            
            if (!in_array(strtolower($typeValue), ['service', 'good'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Type must be 'service' or 'good', got '{$typeValue}'";
                $this->errorCount++;
                return null;
            }
            
            // Find default price column
            $defaultPrice = $row['default_price'] ?? null;
            if (empty($defaultPrice) || !is_numeric($defaultPrice)) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Default price is required and must be a number";
                $this->errorCount++;
                return null;
            }
            
            // Find VAT rate column
            $vatRate = $row['vat_rate'] ?? null;
            if (!empty($vatRate) && (!is_numeric($vatRate) || $vatRate < 1 || $vatRate > 100)) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": VAT rate must be a number between 1 and 100";
                $this->errorCount++;
                return null;
            }
            $vatRate = !empty($vatRate) ? (float) $vatRate : 0.00;
            
            // Find hospital share column - try different variations
            $hospitalShareValue = $row['hospital_share'] ?? $row['hospital_share_'] ?? null;
            
            if (empty($hospitalShareValue) || !is_numeric($hospitalShareValue)) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Hospital share is required and must be a number";
                $this->errorCount++;
                return null;
            }
            
            $hospitalShare = (int) $hospitalShareValue;
            if ($hospitalShare < 0 || $hospitalShare > 100) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Hospital share must be between 0 and 100";
                $this->errorCount++;
                return null;
            }

            // Find related entities using flexible column access
            $group = null;
            $groupName = $row['group_name'] ?? null;
            if (!empty($groupName)) {
                $group = Group::where('business_id', $this->businessId)
                    ->where('name', trim($groupName))
                    ->first();
            }

            $subgroup = null;
            $subgroupName = $row['subgroup_name'] ?? null;
            if (!empty($subgroupName)) {
                $subgroup = Group::where('business_id', $this->businessId)
                    ->where('name', trim($subgroupName))
                    ->first();
            }

            $department = null;
            $departmentName = $row['department_name'] ?? null;
            if (!empty($departmentName)) {
                $department = Department::where('business_id', $this->businessId)
                    ->where('name', trim($departmentName))
                    ->first();
            }

            $itemUnit = null;
            $unitName = $row['unit_of_measure'] ?? null;
            if (!empty($unitName)) {
                $itemUnit = ItemUnit::where('business_id', $this->businessId)
                    ->where('name', trim($unitName))
                    ->first();
            }

            // Validate hospital share and contractor relationship using the service
            $contractorUsername = $row['contractor_username'] ?? null;
            $validationResult = ContractorValidationService::validateHospitalShareContractor(
                $hospitalShare, 
                $contractorUsername, 
                $this->businessId, 
                $this->getRowNumber() + 1
            );
            
            if (!$validationResult['isValid']) {
                foreach ($validationResult['errors'] as $error) {
                    $this->errors[] = $error;
                }
                $this->errorCount++;
                return null;
            }
            
            $contractor = $validationResult['contractor'];

            // Handle code - check for duplicates and auto-generate if needed
            $code = $row['code_auto_generated_if_empty'] ?? $row['code'] ?? null;
            if (!empty($code)) {
                $code = trim($code);
            }
            
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
            
            // Create the item with default pricing as fallback
            $description = $row['description'] ?? null;
            $otherNames = $row['other_names'] ?? null;
            
            $item = new Item([
                'name' => trim($row['name']),
                'code' => $code, // Will be auto-generated if empty
                'type' => strtolower($typeValue),
                'description' => !empty($description) ? trim($description) : null,
                'group_id' => $group ? $group->id : null,
                'subgroup_id' => $subgroup ? $subgroup->id : null,
                'department_id' => $department ? $department->id : null,
                'uom_id' => $itemUnit ? $itemUnit->id : null,
                'service_point_id' => null, // Removed global service point
                'default_price' => (float) $defaultPrice, // Default price as fallback
                'vat_rate' => $vatRate,
                'hospital_share' => $hospitalShare,
                'contractor_account_id' => $contractor ? $contractor->id : null,
                'business_id' => $this->businessId,
                'other_names' => !empty($otherNames) ? trim($otherNames) : null,
            ]);

            $this->successCount++;
            
            // Process dynamic branch columns for pricing and service points
            $this->processBranchData($row, $item);
            
            // Debug logging
            Log::info("Processed item '{$item->name}'. Current service points queue: " . count($this->branchServicePoints));
            
            return $item;

        } catch (\Exception $e) {
            $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }
    
    /**
     * Process branch-specific pricing and service point data from dynamic columns
     */
    private function processBranchData(array $row, Item $item)
    {
        // Debug: Log available columns with detailed analysis
        $availableColumns = array_keys($row);
        Log::info("=== DEBUG: Available columns for item '{$item->name}' ===");
        Log::info("Total columns: " . count($availableColumns));
        Log::info("Columns: " . implode(', ', $availableColumns));
        
        // Show which columns might be service point columns
        $servicePointColumns = array_filter($availableColumns, function($col) {
            return stripos($col, 'service') !== false || stripos($col, 'point') !== false;
        });
        Log::info("Potential service point columns: " . implode(', ', $servicePointColumns));
        
        // Show which columns might be price columns
        $priceColumns = array_filter($availableColumns, function($col) {
            return stripos($col, 'price') !== false;
        });
        Log::info("Potential price columns: " . implode(', ', $priceColumns));
        
        // Get all branches for this business
        $branches = Branch::where('business_id', $this->businessId)->get();
        
        foreach ($branches as $branch) {
            // Process pricing columns - use normalized pattern matching
            $priceColumnPattern = $this->normalizeColumnName($branch->name . '_price');
            $priceValue = $this->findColumnValueByNormalizedKey($row, $priceColumnPattern);
            
            // Use default price if branch price is empty or not provided
            $finalPrice = null;
            if (!empty($priceValue) && is_numeric($priceValue)) {
                $finalPrice = (float) $priceValue;
            } else {
                // Use default price as fallback
                $finalPrice = $item->default_price;
            }
            
            // Always create a branch price record (either with specific price or default price)
            if ($finalPrice > 0) {
                $this->branchPrices[] = [
                    'item_code' => $item->code ?? $item->name, // Use code or name for later lookup
                    'branch_id' => $branch->id,
                    'price' => $finalPrice
                ];
            }
            
            // Process service point columns - use normalized pattern matching
            $servicePointColumnPattern = $this->normalizeColumnName($branch->name . '_service_point');
            $servicePointValue = $this->findColumnValueByNormalizedKey($row, $servicePointColumnPattern);
            
            // Debug logging - always log the attempt
            Log::info("Attempting to find service point for branch '{$branch->name}' using normalized pattern '{$servicePointColumnPattern}'");
            if (!empty($servicePointValue)) {
                Log::info("✓ Found service point value '{$servicePointValue}' for branch '{$branch->name}'");
            } else {
                Log::warning("✗ No service point value found for branch '{$branch->name}' with normalized pattern '{$servicePointColumnPattern}'");
            }
            
            if (!empty($servicePointValue)) {
                // Find the service point by name (first try branch-specific, then any in business)
                $servicePoint = ServicePoint::where('business_id', $this->businessId)
                    ->where('name', trim($servicePointValue))
                    ->where(function($query) use ($branch) {
                        $query->where('branch_id', $branch->id)
                              ->orWhereNull('branch_id'); // Allow global service points
                    })
                    ->first();
                
                // If not found with branch filter, try without branch filter (fallback)
                if (!$servicePoint) {
                    $servicePoint = ServicePoint::where('business_id', $this->businessId)
                        ->where('name', trim($servicePointValue))
                        ->first();
                }
                
                if ($servicePoint) {
                    $this->branchServicePoints[] = [
                        'item_code' => $item->code ?? $item->name, // Use code or name for later lookup
                        'branch_id' => $branch->id,
                        'service_point_id' => $servicePoint->id
                    ];
                } else {
                    // Log missing service point for debugging
                    Log::warning("Service point '{$servicePointValue}' not found for branch '{$branch->name}' in business {$this->businessId}");
                }
            }
        }
    }
    
    /**
     * Normalize column name for matching (remove special characters, convert to lowercase)
     */
    private function normalizeColumnName($columnName)
    {
        // First trim any whitespace
        $normalized = trim($columnName);
        
        // Convert to lowercase
        $normalized = strtolower($normalized);
        
        // Replace spaces and dashes with underscores
        $normalized = preg_replace('/[\s\-]+/', '_', $normalized);
        
        // Remove parentheses and other special characters except underscores
        $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized);
        
        // Remove multiple underscores
        $normalized = preg_replace('/_+/', '_', $normalized);
        
        // Remove leading/trailing underscores
        $normalized = trim($normalized, '_');
        
        return $normalized;
    }
    
    /**
     * Find column value by trying different normalized variations
     */
    private function findColumnValue(array $row, $normalizedKey)
    {
        foreach ($row as $key => $value) {
            if ($this->normalizeColumnName($key) === $normalizedKey) {
                return $value;
            }
        }
        return null;
    }
    
    /**
     * Find column value by normalized key (for direct matching)
     */
    private function findColumnValueByNormalizedKey(array $row, $normalizedPattern)
    {
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeColumnName($key);
            if ($normalizedKey === $normalizedPattern) {
                Log::info("✓ Direct normalized match: Column '{$key}' (normalized: '{$normalizedKey}') matches pattern '{$normalizedPattern}'");
                return $value;
            }
        }
        
        Log::warning("✗ No normalized match found for pattern '{$normalizedPattern}'");
        return null;
    }
    
    /**
     * Find column value by exact pattern matching (for dynamic branch columns)
     */
    private function findColumnValueByPattern(array $row, $pattern)
    {
        // First try exact match
        foreach ($row as $key => $value) {
            if (trim($key) === $pattern) {
                return $value;
            }
        }
        
        // Try normalized matching (Laravel Excel converts headers)
        $normalizedPattern = $this->normalizeColumnName($pattern);
        Log::info("Trying normalized pattern: '{$normalizedPattern}' for original pattern: '{$pattern}'");
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeColumnName($key);
            if ($normalizedKey === $normalizedPattern) {
                Log::info("✓ MATCH found! Column '{$key}' (normalized: '{$normalizedKey}') matches pattern '{$normalizedPattern}'");
                return $value;
            }
        }
        
        // Try partial matching for service points
        if (strpos($pattern, ' - Service Point') !== false) {
            $branchName = str_replace(' - Service Point', '', $pattern);
            $possibleKeys = [
                $branchName . '_service_point',
                $branchName . ' service point',
                strtolower($branchName) . '_service_point',
                str_replace(' ', '_', strtolower($branchName)) . '_service_point',
            ];
            
            foreach ($possibleKeys as $possibleKey) {
                foreach ($row as $key => $value) {
                    if ($this->normalizeColumnName($key) === $this->normalizeColumnName($possibleKey)) {
                        Log::info("Found service point using alternative pattern: '{$key}' for pattern '{$pattern}'");
                        return $value;
                    }
                }
            }
        }
        
        // Try partial matching for prices
        if (strpos($pattern, ' - Price') !== false) {
            $branchName = str_replace(' - Price', '', $pattern);
            $possibleKeys = [
                $branchName . '_price',
                $branchName . ' price',
                strtolower($branchName) . '_price',
                str_replace(' ', '_', strtolower($branchName)) . '_price',
            ];
            
            foreach ($possibleKeys as $possibleKey) {
                foreach ($row as $key => $value) {
                    if ($this->normalizeColumnName($key) === $this->normalizeColumnName($possibleKey)) {
                        Log::info("Found price using alternative pattern: '{$key}' for pattern '{$pattern}'");
                        return $value;
                    }
                }
            }
        }
        
        return null;
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
                // Find the item by code or name
                $item = Item::where('business_id', $this->businessId)
                    ->where(function($query) use ($branchPriceData) {
                        $query->where('code', $branchPriceData['item_code'])
                              ->orWhere('name', $branchPriceData['item_code']);
                    })
                    ->first();
                
                if ($item) {
                    BranchItemPrice::create([
                        'business_id' => $this->businessId,
                        'item_id' => $item->id,
                        'branch_id' => $branchPriceData['branch_id'],
                        'price' => $branchPriceData['price']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error creating branch price: ' . $e->getMessage());
            }
        }
        
        Log::info('Created ' . count($this->branchPrices) . ' branch prices for imported items');
    }
    
    public function createBranchServicePoints()
    {
        // Create branch service points for imported items
        foreach ($this->branchServicePoints as $branchServicePointData) {
            try {
                // Find the item by code or name
                $item = Item::where('business_id', $this->businessId)
                    ->where(function($query) use ($branchServicePointData) {
                        $query->where('code', $branchServicePointData['item_code'])
                              ->orWhere('name', $branchServicePointData['item_code']);
                    })
                    ->first();
                
                if ($item) {
                    BranchServicePoint::create([
                        'business_id' => $this->businessId,
                        'item_id' => $item->id,
                        'branch_id' => $branchServicePointData['branch_id'],
                        'service_point_id' => $branchServicePointData['service_point_id']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error creating branch service point: ' . $e->getMessage());
            }
        }
        
        Log::info('Created ' . count($this->branchServicePoints) . ' branch service points for imported items');
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