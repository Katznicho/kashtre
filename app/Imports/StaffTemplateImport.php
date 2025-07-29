<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class StaffTemplateImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $businessId;
    protected $branchId;

    public function __construct($businessId, $branchId)
    {
        $this->businessId = $businessId;
        $this->branchId = $branchId;
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['surname']) || empty($row['first_name']) || empty($row['email'])) {
            return null;
        }

        // Build full name - check multiple possible column names
        $surname = '';
        $firstName = '';
        $middleName = '';
        
        // Surname variations
        $surnameVariations = ['surname', 'Surname'];
        foreach ($surnameVariations as $field) {
            if (isset($row[$field])) {
                $surname = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // First name variations
        $firstNameVariations = ['first_name', 'first name', 'First Name'];
        foreach ($firstNameVariations as $field) {
            if (isset($row[$field])) {
                $firstName = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Middle name variations
        $middleNameVariations = ['middle_name', 'middle name', 'Middle Name'];
        foreach ($middleNameVariations as $field) {
            if (isset($row[$field])) {
                $middleName = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Build full name
        $name = trim($surname . ' ' . $firstName . ' ' . $middleName);

        // Clean and validate data - check multiple possible column names
        $email = '';
        $phone = '';
        $nin = '';
        $gender = 'male'; // default
        
        // Email variations
        $emailVariations = ['email', 'Email'];
        foreach ($emailVariations as $field) {
            if (isset($row[$field])) {
                $email = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Phone variations
        $phoneVariations = ['phone', 'Phone'];
        foreach ($phoneVariations as $field) {
            if (isset($row[$field])) {
                $phone = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // NIN variations
        $ninVariations = ['nin', 'NIN'];
        foreach ($ninVariations as $field) {
            if (isset($row[$field])) {
                $nin = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Gender variations
        $genderVariations = ['gender', 'gender_male_or_female', 'Gender (male or female)'];
        foreach ($genderVariations as $field) {
            if (isset($row[$field])) {
                $genderValue = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                $gender = in_array(strtolower($genderValue), ['male', 'female']) ? strtolower($genderValue) : 'male';
                break;
            }
        }
        
        // Check if contractor - convert to lowercase and trim
        // Check multiple possible column names for contractor field
        $contractorField = null;
        $possibleNames = [
            'is_contractor', 
            'is contractor', 
            'contractor', 
            'iscontractor',
            'Is Contractor (Yes or No)',
            'is_contractor_(yes_or_no)',
            'is_contractor_yes_or_no',
            'iscontractor_yes_or_no'
        ];
        
        foreach ($possibleNames as $contractorFieldName) {
            if (isset($row[$contractorFieldName])) {
                $contractorField = $contractorFieldName;
                break;
            }
        }
        
        $contractorValue = '';
        if ($contractorField) {
            $contractorValue = strtolower(trim($row[$contractorField] ?? ''));
        }
        
        $isContractor = $contractorValue === 'yes';
        
        // Contractor fields - check multiple possible column names
        $bankName = '';
        $accountName = '';
        $accountNumber = '';
        
        // Bank name variations
        $bankNameVariations = ['bank_name', 'bank name', 'Bank Name'];
        foreach ($bankNameVariations as $field) {
            if (isset($row[$field])) {
                $bankName = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Account name variations
        $accountNameVariations = ['account_name', 'account name', 'Account Name'];
        foreach ($accountNameVariations as $field) {
            if (isset($row[$field])) {
                $accountName = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Account number variations
        $accountNumberVariations = ['account_number', 'account number', 'Account Number'];
        foreach ($accountNumberVariations as $field) {
            if (isset($row[$field])) {
                $accountNumber = is_string($row[$field]) ? $row[$field] : (string)$row[$field];
                break;
            }
        }
        
        // Set default permissions based on contractor status
        if ($isContractor) {
            $permissions = [
                'Contractor',
                'View Contractor',
                'Edit Contractor', 
                'Add Contractor'
            ];
        } else {
            $permissions = ['View Dashboard'];
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'nin' => $nin,
            'gender' => $gender,
            'status' => 'active', // Default status
            'business_id' => $this->businessId,
            'branch_id' => $this->branchId,
            'qualification_id' => null, // Will be set later if needed
            'title_id' => null, // Will be set later if needed
            'department_id' => null, // Will be set later if needed
            'section_id' => null, // Will be set later if needed
            'service_points' => [], // Default empty array
            'allowed_branches' => [$this->branchId],
            'permissions' => $permissions,
            'password' => '', // Empty password for password reset
        ]);

        // Save the user first
        $user->save();

        // Create contractor profile if needed
        if ($isContractor) {
            try {
                $contractorProfile = \App\Models\ContractorProfile::create([
                    'user_id' => $user->id,
                    'business_id' => $this->businessId,
                    'bank_name' => $bankName,
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                ]);
            } catch (\Exception $e) {
                // Handle error silently
            }
        }

        return $user;
    }

    public function rules(): array
    {
        return [
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable',
            'nin' => 'nullable',
            'gender' => 'nullable|in:male,female',
            'is_contractor' => 'nullable|in:Yes,No,yes,no',
            'bank_name' => 'nullable',
            'account_name' => 'nullable',
            'account_number' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'surname.required' => 'Surname is required.',
            'first_name.required' => 'First name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email already exists.',
            'gender.in' => 'Gender must be male or female.',
            'is_contractor.in' => 'Is Contractor must be Yes or No.',
        ];
    }


} 