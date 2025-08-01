<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Item;
use App\Models\Business;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;
use App\Models\Branch;
use App\Models\BranchItemPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ItemTemplateImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $businessId;
    protected $business;
    protected $branchPricesToCreate = [];

    public function __construct($businessId = null)
    {
        $this->businessId = $businessId;
        if ($businessId) {
            $this->business = Business::find($businessId);
        }
    }

    /**
     * Normalize header names to handle different camel cases and variations
     */
    protected function normalizeHeaderName($headerName)
    {
        if (empty($headerName)) return '';
        
        // Convert to lowercase and remove special characters
        $normalized = strtolower(trim($headerName));
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }

    /**
     * Get value from row with flexible header matching
     */
    protected function getRowValue($row, $possibleHeaders)
    {
        foreach ($possibleHeaders as $header) {
            $normalizedHeader = $this->normalizeHeaderName($header);
            
            foreach ($row as $key => $value) {
                $normalizedKey = $this->normalizeHeaderName($key);
                if ($normalizedKey === $normalizedHeader) {
                    return $value;
                }
            }
        }
        return null;
    }

    public function model(array $row)
    {
        // Skip empty rows
        $name = $this->getRowValue($row, ['name', 'Name', 'NAME', 'item_name', 'Item Name']);
        $code = $this->getRowValue($row, ['code', 'Code', 'CODE', 'item_code', 'Item Code']);
        
        if (empty($name) || empty($code)) {
            return null;
        }

        try {
            // Use the business from constructor if provided, otherwise fall back to business_name
            $business = $this->business;
            
            $businessName = $this->getRowValue($row, [
                'business_name', 'Business Name', 'BUSINESS_NAME', 'business', 'Business'
            ]);
            
            if (!$business && !empty($businessName)) {
                $business = Business::where('name', trim($businessName))->first();
                if (!$business) {
                    $this->addError("Business '{$businessName}' not found");
                    return null;
                }
            }
            
            if (!$business) {
                $this->addError("No business specified for item '{$name}'");
                return null;
            }

            // Check business access permissions
            if (Auth::user()->business_id != 1 && Auth::user()->business_id != $business->id) {
                $this->addError("You don't have permission to create items for business '{$business->name}'");
                return null;
            }

            // Check if item code already exists
            $existingItem = Item::where('code', trim($code))->first();
            if ($existingItem) {
                $this->addError("Item with code '{$code}' already exists");
                return null;
            }

            // Validate type
            $validTypes = ['service', 'good', 'package', 'bulk'];
            $type = strtolower(trim($this->getRowValue($row, [
                'type_(service/good/package/bulk)', 'type', 'Type', 'TYPE', 'item_type', 'Item Type'
            ]) ?? ''));
            if (!in_array($type, $validTypes)) {
                $this->addError("Invalid type '{$type}'. Must be one of: " . implode(', ', $validTypes));
                return null;
            }

            // Find related entities by name (from reference sheet)
            $group = null;
            $groupName = $this->getRowValue($row, [
                'group_name', 'Group Name', 'GROUP_NAME', 'group', 'Group'
            ]);
            if (!empty($groupName)) {
                $group = Group::where('name', trim($groupName))->where('business_id', $business->id)->first();
                if (!$group) {
                    $this->addError("Group '{$groupName}' not found for business '{$business->name}'");
                    return null;
                }
            }

            $subgroup = null;
            $subgroupName = $this->getRowValue($row, [
                'subgroup_name', 'Subgroup Name', 'SUBGROUP_NAME', 'subgroup', 'Subgroup'
            ]);
            if (!empty($subgroupName)) {
                // Subgroups are stored in the groups table
                $subgroup = Group::where('name', trim($subgroupName))->where('business_id', $business->id)->first();
                if (!$subgroup) {
                    $this->addError("Subgroup '{$subgroupName}' not found for business '{$business->name}'");
                    return null;
                }
            }

            $department = null;
            $departmentName = $this->getRowValue($row, [
                'department_name', 'Department Name', 'DEPARTMENT_NAME', 'department', 'Department'
            ]);
            if (!empty($departmentName)) {
                $department = Department::where('name', trim($departmentName))->where('business_id', $business->id)->first();
                if (!$department) {
                    $this->addError("Department '{$departmentName}' not found for business '{$business->name}'");
                    return null;
                }
            }

            $itemUnit = null;
            $unitName = $this->getRowValue($row, [
                'unit_of_measure', 'Unit of Measure', 'UNIT_OF_MEASURE', 'unit', 'Unit', 'uom', 'UOM'
            ]);
            if (!empty($unitName)) {
                $itemUnit = ItemUnit::where('name', trim($unitName))->where('business_id', $business->id)->first();
                if (!$itemUnit) {
                    $this->addError("Unit of measure '{$unitName}' not found for business '{$business->name}'");
                    return null;
                }
            }

            $servicePoint = null;
            $servicePointName = $this->getRowValue($row, [
                'service_point_name', 'Service Point Name', 'SERVICE_POINT_NAME', 'service_point', 'Service Point'
            ]);
            if (!empty($servicePointName)) {
                $servicePoint = ServicePoint::where('name', trim($servicePointName))->where('business_id', $business->id)->first();
                if (!$servicePoint) {
                    $this->addError("Service point '{$servicePointName}' not found for business '{$business->name}'");
                    return null;
                }
            }

            // Handle contractor validation
            $contractor = null;
            $hospitalShare = (int) ($this->getRowValue($row, [
                'hospital_share_(%)', 'hospital_share', 'Hospital Share (%)', 'HOSPITAL_SHARE', 'hospital_share_percent'
            ]) ?? 100);
            if ($hospitalShare != 100) {
                $contractorAccountNumber = $this->getRowValue($row, [
                    'contractor_kashtre_account_number', 'Contractor Kashtre Account Number', 
                    'CONTRACTOR_KASHTRE_ACCOUNT_NUMBER', 'contractor_account', 'Contractor Account'
                ]);
                
                if (!empty($contractorAccountNumber)) {
                    $contractor = ContractorProfile::where('kashtre_account_number', trim($contractorAccountNumber))
                        ->where('business_id', $business->id)
                        ->first();
                    
                    if (!$contractor) {
                        $this->addError("Contractor with kashtre account number '{$contractorAccountNumber}' not found for business '{$business->name}'");
                        return null;
                    }
                } else {
                    $this->addError("Contractor kashtre account number is required when hospital share is not 100%");
                    return null;
                }
            }

            // Validate pricing type
            $pricingType = strtolower(trim($this->getRowValue($row, [
                'pricing_type_(default/custom)', 'pricing_type', 'Pricing Type (default/custom)', 'Pricing Type', 'PRICING_TYPE'
            ]) ?? 'default'));
            if (!in_array($pricingType, ['default', 'custom'])) {
                $this->addError("Invalid pricing type '{$pricingType}'. Must be 'default' or 'custom'");
                return null;
            }

            // Store branch price info for later creation
            $branchPriceInfo = null;
            if ($pricingType === 'custom') {
                $branchName = $this->getRowValue($row, [
                    'branch_name', 'Branch Name', 'BRANCH_NAME', 'branch', 'Branch'
                ]);
                $branchPrice = $this->getRowValue($row, [
                    'branch_price', 'Branch Price', 'BRANCH_PRICE', 'price', 'Price'
                ]);
                
                if (!empty($branchName) && !empty($branchPrice)) {
                    $branch = Branch::where('name', trim($branchName))->where('business_id', $business->id)->first();
                    if ($branch) {
                        $branchPriceInfo = [
                            'branch_id' => $branch->id,
                            'price' => (float) $branchPrice
                        ];
                    } else {
                        $this->addError("Branch '{$branchName}' not found for business '{$business->name}'");
                        return null;
                    }
                }
            }

            // Create the item with proper field mapping
            $item = new Item([
                'business_id' => $business->id,
                'name' => trim($name),
                'code' => trim($code),
                'type' => $type,
                'description' => trim($this->getRowValue($row, [
                    'description', 'Description', 'DESCRIPTION', 'desc', 'Desc'
                ]) ?? ''),
                'group_id' => $group?->id,
                'subgroup_id' => $subgroup?->id,
                'department_id' => $department?->id,
                'uom_id' => $itemUnit?->id,
                'service_point_id' => $servicePoint?->id,
                'default_price' => (float) ($this->getRowValue($row, [
                    'default_price', 'Default Price', 'DEFAULT_PRICE', 'price', 'Price'
                ]) ?? 0),
                'hospital_share' => $hospitalShare,
                'contractor_account_id' => $contractor?->id,
                'other_names' => trim($this->getRowValue($row, [
                    'other_names', 'Other Names', 'OTHER_NAMES', 'alternative_names', 'Alternative Names'
                ]) ?? ''),
            ]);

            // Store branch price info for later creation
            if ($branchPriceInfo) {
                $this->branchPricesToCreate[] = [
                    'item_code' => trim($code),
                    'branch_price_info' => $branchPriceInfo
                ];
            }

            $this->successCount++;
            return $item;

        } catch (\Exception $e) {
            $this->addError("Error processing row: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create branch prices after items are imported
     */
    public function createBranchPrices()
    {
        foreach ($this->branchPricesToCreate as $branchPriceData) {
            try {
                $item = Item::where('code', $branchPriceData['item_code'])->first();
                if ($item) {
                    BranchItemPrice::create([
                        'business_id' => $item->business_id,
                        'branch_id' => $branchPriceData['branch_price_info']['branch_id'],
                        'item_id' => $item->id,
                        'price' => $branchPriceData['branch_price_info']['price'],
                    ]);
                }
            } catch (\Exception $e) {
                $this->addError("Error creating branch price for item '{$branchPriceData['item_code']}': " . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'type' => 'required|in:service,good,package,bulk',
            'description' => 'nullable|string',
            'group_name' => 'nullable|string|max:255',
            'subgroup_name' => 'nullable|string|max:255',
            'department_name' => 'nullable|string|max:255',
            'unit_of_measure' => 'nullable|string|max:255',
            'service_point_name' => 'nullable|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'hospital_share' => 'required|integer|between:0,100',
            'contractor_kashtre_account_number' => 'nullable|string|max:255',
            'other_names' => 'nullable|string',
            'pricing_type' => 'required|in:default,custom',
            'branch_name' => 'nullable|string|max:255',
            'branch_price' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name must not exceed 255 characters.',
            'code.required' => 'Code is required.',
            'code.string' => 'Code must be a string.',
            'code.max' => 'Code must not exceed 255 characters.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type must be one of: service, good, package, bulk.',
            'description.string' => 'Description must be a string.',
            'group_name.string' => 'Group name must be a string.',
            'group_name.max' => 'Group name must not exceed 255 characters.',
            'subgroup_name.string' => 'Subgroup name must be a string.',
            'subgroup_name.max' => 'Subgroup name must not exceed 255 characters.',
            'department_name.string' => 'Department name must be a string.',
            'department_name.max' => 'Department name must not exceed 255 characters.',
            'unit_of_measure.string' => 'Unit of measure must be a string.',
            'unit_of_measure.max' => 'Unit of measure must not exceed 255 characters.',
            'service_point_name.string' => 'Service point name must be a string.',
            'service_point_name.max' => 'Service point name must not exceed 255 characters.',
            'default_price.required' => 'Default price is required.',
            'default_price.numeric' => 'Default price must be a number.',
            'default_price.min' => 'Default price must be a positive number.',
            'hospital_share.required' => 'Hospital share (%) is required.',
            'hospital_share.integer' => 'Hospital share (%) must be an integer.',
            'hospital_share.between' => 'Hospital share (%) must be between 0 and 100.',
            'contractor_kashtre_account_number.string' => 'Contractor kashtre account number must be a string.',
            'contractor_kashtre_account_number.max' => 'Contractor kashtre account number must not exceed 255 characters.',
            'other_names.string' => 'Other names must be a string.',
            'pricing_type.required' => 'Pricing type is required.',
            'pricing_type.in' => 'Pricing type must be default or custom.',
            'branch_name.string' => 'Branch name must be a string.',
            'branch_name.max' => 'Branch name must not exceed 255 characters.',
            'branch_price.numeric' => 'Branch price must be a number.',
            'branch_price.min' => 'Branch price must be a positive number.',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    protected function addError($message)
    {
        $this->errors[] = $message;
        $this->errorCount++;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }
} 