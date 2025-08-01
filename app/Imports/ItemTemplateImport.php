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

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['business_name']) || empty($row['name']) || empty($row['code'])) {
            return null;
        }

        try {
            // Find business by name
            $business = Business::where('name', trim($row['business_name']))->first();
            if (!$business) {
                $this->addError("Business '{$row['business_name']}' not found");
                return null;
            }

            // Check business access permissions
            if (Auth::user()->business_id != 1 && Auth::user()->business_id != $business->id) {
                $this->addError("You don't have permission to create items for business '{$row['business_name']}'");
                return null;
            }

            // Check if item code already exists
            $existingItem = Item::where('code', trim($row['code']))->first();
            if ($existingItem) {
                $this->addError("Item with code '{$row['code']}' already exists");
                return null;
            }

            // Validate type
            $validTypes = ['service', 'good', 'package', 'bulk'];
            $type = strtolower(trim($row['type'] ?? ''));
            if (!in_array($type, $validTypes)) {
                $this->addError("Invalid type '{$type}'. Must be one of: " . implode(', ', $validTypes));
                return null;
            }

            // Find related entities
            $group = null;
            if (!empty($row['group_name'])) {
                $group = Group::where('name', trim($row['group_name']))->where('business_id', $business->id)->first();
                if (!$group) {
                    $this->addError("Group '{$row['group_name']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            }

            $subgroup = null;
            if (!empty($row['subgroup_name'])) {
                $subgroup = Group::where('name', trim($row['subgroup_name']))->where('business_id', $business->id)->first();
                if (!$subgroup) {
                    $this->addError("Subgroup '{$row['subgroup_name']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            }

            $department = null;
            if (!empty($row['department_name'])) {
                $department = Department::where('name', trim($row['department_name']))->where('business_id', $business->id)->first();
                if (!$department) {
                    $this->addError("Department '{$row['department_name']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            }

            $itemUnit = null;
            if (!empty($row['unit_of_measure'])) {
                $itemUnit = ItemUnit::where('name', trim($row['unit_of_measure']))->where('business_id', $business->id)->first();
                if (!$itemUnit) {
                    $this->addError("Unit of measure '{$row['unit_of_measure']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            }

            $servicePoint = null;
            if (!empty($row['service_point_name'])) {
                $servicePoint = ServicePoint::where('name', trim($row['service_point_name']))->where('business_id', $business->id)->first();
                if (!$servicePoint) {
                    $this->addError("Service point '{$row['service_point_name']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            }

            // Handle contractor validation
            $contractor = null;
            $hospitalShare = (int) ($row['hospital_share'] ?? 100);
            if ($hospitalShare != 100 && !empty($row['contractor_email'])) {
                $contractor = ContractorProfile::whereHas('user', function($query) use ($row) {
                    $query->where('email', trim($row['contractor_email']));
                })->where('business_id', $business->id)->first();
                
                if (!$contractor) {
                    $this->addError("Contractor with email '{$row['contractor_email']}' not found for business '{$row['business_name']}'");
                    return null;
                }
            } elseif ($hospitalShare != 100 && empty($row['contractor_email'])) {
                $this->addError("Contractor email is required when hospital share is not 100%");
                return null;
            }

            // Validate pricing type
            $pricingType = strtolower(trim($row['pricing_type'] ?? 'default'));
            if (!in_array($pricingType, ['default', 'custom'])) {
                $this->addError("Invalid pricing type '{$pricingType}'. Must be 'default' or 'custom'");
                return null;
            }

            // Create the item
            $item = new Item([
                'business_id' => $business->id,
                'name' => trim($row['name']),
                'code' => trim($row['code']),
                'type' => $type,
                'description' => trim($row['description'] ?? ''),
                'group_id' => $group?->id,
                'subgroup_id' => $subgroup?->id,
                'department_id' => $department?->id,
                'uom_id' => $itemUnit?->id,
                'service_point_id' => $servicePoint?->id,
                'default_price' => (float) ($row['default_price'] ?? 0),
                'hospital_share' => $hospitalShare,
                'contractor_account_id' => $contractor?->id,
                'other_names' => trim($row['other_names'] ?? ''),
            ]);

            $this->successCount++;
            return $item;

        } catch (\Exception $e) {
            $this->addError("Error processing row: " . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
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
            'contractor_email' => 'nullable|email',
            'other_names' => 'nullable|string',
            'pricing_type' => 'required|in:default,custom',
            'branch_name' => 'nullable|string|max:255',
            'branch_price' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'business_name.required' => 'Business name is required.',
            'name.required' => 'Item name is required.',
            'code.required' => 'Item code is required.',
            'type.required' => 'Item type is required.',
            'type.in' => 'Item type must be service, good, package, or bulk.',
            'default_price.required' => 'Default price is required.',
            'default_price.numeric' => 'Default price must be a number.',
            'default_price.min' => 'Default price must be 0 or greater.',
            'hospital_share.required' => 'Hospital share is required.',
            'hospital_share.integer' => 'Hospital share must be a whole number.',
            'hospital_share.between' => 'Hospital share must be between 0 and 100.',
            'contractor_email.email' => 'Contractor email must be a valid email address.',
            'pricing_type.required' => 'Pricing type is required.',
            'pricing_type.in' => 'Pricing type must be default or custom.',
            'branch_price.numeric' => 'Branch price must be a number.',
            'branch_price.min' => 'Branch price must be 0 or greater.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
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