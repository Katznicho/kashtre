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

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    public function model(array $row)
    {
        try {

            
            // Find the type column - use the correct column name from logs
            $typeValue = $row['type_servicegood'] ?? null;
            
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
            
            if (empty($row['default_price']) || !is_numeric($row['default_price'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Default price is required and must be a number";
                $this->errorCount++;
                return null;
            }
            
            if (empty($row['hospital_share']) || !is_numeric($row['hospital_share'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Hospital share is required and must be a number";
                $this->errorCount++;
                return null;
            }
            
            $hospitalShare = (int) $row['hospital_share'];
            if ($hospitalShare < 0 || $hospitalShare > 100) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Hospital share must be between 0 and 100";
                $this->errorCount++;
                return null;
            }

            // Validate type is only service or good
            if (!in_array(strtolower($typeValue), ['service', 'good'])) {
                $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": Type must be 'service' or 'good', got '{$typeValue}'";
                $this->errorCount++;
                return null;
            }

            // Find related entities
            $group = null;
            if (!empty($row['group_name'])) {
                $group = Group::where('business_id', $this->businessId)
                    ->where('name', trim($row['group_name']))
                    ->first();
            }

            $subgroup = null;
            if (!empty($row['subgroup_name'])) {
                $subgroup = Group::where('business_id', $this->businessId)
                    ->where('name', trim($row['subgroup_name']))
                    ->first();
            }

            $department = null;
            if (!empty($row['department_name'])) {
                $department = Department::where('business_id', $this->businessId)
                    ->where('name', trim($row['department_name']))
                    ->first();
            }

            $itemUnit = null;
            if (!empty($row['unit_of_measure'])) {
                $itemUnit = ItemUnit::where('business_id', $this->businessId)
                    ->where('name', trim($row['unit_of_measure']))
                    ->first();
            }

            $servicePoint = null;
            if (!empty($row['service_point_name'])) {
                $servicePoint = ServicePoint::where('business_id', $this->businessId)
                    ->where('name', trim($row['service_point_name']))
                    ->first();
            }

            $contractor = null;
            if (!empty($row['contractor_kashtre_account_number'])) {
                $contractor = ContractorProfile::where('business_id', $this->businessId)
                    ->where('kashtre_account_number', trim($row['contractor_kashtre_account_number']))
                    ->first();
            }

            // Validate hospital share and contractor relationship
            $hospitalShare = (int) $row['hospital_share'];
            if ($hospitalShare < 100 && !$contractor) {
                // Instead of failing, set hospital share to 100% for items without contractors
                $hospitalShare = 100;
                Log::info("Row " . ($this->getRowNumber() + 1) . ": Auto-adjusted hospital share to 100% for item without contractor");
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
            
            // Check pricing type for custom pricing
            $pricingType = $row['pricing_type_defaultcustom'] ?? 'default';

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
            
            // Create the item
            $item = new Item([
                'name' => trim($row['name']),
                'code' => $code, // Will be auto-generated if empty
                'type' => strtolower($typeValue),
                'description' => !empty($row['description']) ? trim($row['description']) : null,
                'group_id' => $group ? $group->id : null,
                'subgroup_id' => $subgroup ? $subgroup->id : null,
                'department_id' => $department ? $department->id : null,
                'uom_id' => $itemUnit ? $itemUnit->id : null,
                'service_point_id' => $servicePoint ? $servicePoint->id : null,
                'default_price' => (float) $row['default_price'],
                'hospital_share' => $hospitalShare,
                'contractor_account_id' => $contractor ? $contractor->id : null,
                'business_id' => $this->businessId,
                'other_names' => !empty($row['other_names']) ? trim($row['other_names']) : null,
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
            
            return $item;

        } catch (\Exception $e) {
            $this->errors[] = "Row " . ($this->getRowNumber() + 1) . ": " . $e->getMessage();
            $this->errorCount++;
            return null;
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
                    'price' => $branchPriceData['price']
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating branch price: ' . $e->getMessage());
            }
        }
        
        Log::info('Created ' . count($this->branchPrices) . ' branch prices for imported items');
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