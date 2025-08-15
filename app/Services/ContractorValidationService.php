<?php

namespace App\Services;

use App\Models\ContractorProfile;
use Illuminate\Support\Facades\Log;

class ContractorValidationService
{
    /**
     * Get available contractors for a business
     */
    public static function getAvailableContractors($businessId)
    {
        return ContractorProfile::with('user')
            ->where('business_id', $businessId)
            ->get()
            ->pluck('user.name')
            ->filter()
            ->toArray();
    }

    /**
     * Validate hospital share and contractor relationship
     */
    public static function validateHospitalShareContractor($hospitalShare, $contractorUsername, $businessId, $rowNumber = null)
    {
        $errors = [];
        
        // Validate hospital share range
        if ($hospitalShare < 0 || $hospitalShare > 100) {
            $errors[] = self::formatError($rowNumber, "Hospital share must be between 0 and 100, got {$hospitalShare}");
        }
        
        // Find contractor if username is provided
        $contractor = null;
        if (!empty($contractorUsername)) {
            $contractor = ContractorProfile::with('user')
                ->where('business_id', $businessId)
                ->whereHas('user', function($query) use ($contractorUsername) {
                    $query->where('name', trim($contractorUsername));
                })
                ->first();
                
            if (!$contractor) {
                $errors[] = self::formatError($rowNumber, "Contractor '{$contractorUsername}' not found in this business");
            }
        }
        
        // Validate business rules
        if ($hospitalShare < 100 && !$contractor) {
            $errors[] = self::formatError($rowNumber, "Contractor is required when hospital share is less than 100%. Please select a contractor or set hospital share to 100%.");
        }
        
        if ($hospitalShare == 100 && $contractor) {
            $errors[] = self::formatError($rowNumber, "Contractor should not be selected when hospital share is 100%. Please remove the contractor or reduce hospital share.");
        }
        
        return [
            'errors' => $errors,
            'contractor' => $contractor,
            'isValid' => empty($errors)
        ];
    }

    /**
     * Get contractor by username
     */
    public static function getContractorByUsername($username, $businessId)
    {
        return ContractorProfile::with('user')
            ->where('business_id', $businessId)
            ->whereHas('user', function($query) use ($username) {
                $query->where('name', trim($username));
            })
            ->first();
    }

    /**
     * Format error message with row number
     */
    private static function formatError($rowNumber, $message)
    {
        if ($rowNumber) {
            return "Row {$rowNumber}: {$message}";
        }
        return $message;
    }

    /**
     * Get validation rules for Excel templates
     */
    public static function getExcelValidationRules()
    {
        return [
            'hospital_share' => [
                'type' => 'custom',
                'formula' => '=AND(K{row}>=0,K{row}<=100)',
                'error_title' => 'Invalid Hospital Share',
                'error_message' => 'Hospital share must be between 0 and 100',
                'prompt_title' => 'Hospital Share',
                'prompt_message' => 'Enter a value between 0 and 100'
            ],
            'contractor_conditional' => [
                'type' => 'custom',
                'formula' => '=OR(K{row}=100,LEN(L{row})>0)',
                'error_title' => 'Contractor Required',
                'error_message' => 'Contractor is required when hospital share is less than 100%',
                'prompt_title' => 'Contractor Selection',
                'prompt_message' => 'Select a contractor when hospital share < 100%, or leave empty if share = 100%'
            ]
        ];
    }
}
