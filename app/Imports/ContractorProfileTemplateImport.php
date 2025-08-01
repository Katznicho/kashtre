<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\ContractorProfile;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContractorProfileTemplateImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['business_name']) || empty($row['user_email']) || empty($row['bank_name'])) {
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
                $this->addError("You don't have permission to create contractor profiles for business '{$row['business_name']}'");
                return null;
            }

            // Find user by email
            $user = User::where('email', trim($row['user_email']))->first();
            if (!$user) {
                $this->addError("User with email '{$row['user_email']}' not found");
                return null;
            }

            // Check if user belongs to the specified business
            if ($user->business_id != $business->id) {
                $this->addError("User '{$row['user_email']}' does not belong to business '{$row['business_name']}'");
                return null;
            }

            // Check if contractor profile already exists for this user
            $existingProfile = ContractorProfile::where('user_id', $user->id)->first();
            if ($existingProfile) {
                $this->addError("Contractor profile already exists for user '{$row['user_email']}'");
                return null;
            }

            // Clean and validate data
            $accountBalance = is_numeric($row['account_balance'] ?? 0) ? (float) $row['account_balance'] : 0.00;

            $contractorProfile = new ContractorProfile([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'bank_name' => trim($row['bank_name']),
                'account_name' => trim($row['account_name']),
                'account_number' => trim($row['account_number']),
                'account_balance' => $accountBalance,
                'kashtre_account_number' => trim($row['kashtre_account_number'] ?? ''),
                'signing_qualifications' => trim($row['signing_qualifications'] ?? ''),
            ]);

            $this->successCount++;
            return $contractorProfile;

        } catch (\Exception $e) {
            $this->addError("Error processing row: " . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'user_email' => 'required|email',
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_balance' => 'nullable|numeric|min:0',
            'kashtre_account_number' => 'nullable|string|max:255',
            'signing_qualifications' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'business_name.required' => 'Business name is required.',
            'user_email.required' => 'User email is required.',
            'user_email.email' => 'User email must be a valid email address.',
            'bank_name.required' => 'Bank name is required.',
            'account_name.required' => 'Account name is required.',
            'account_number.required' => 'Account number is required.',
            'account_balance.numeric' => 'Account balance must be a number.',
            'account_balance.min' => 'Account balance must be 0 or greater.',
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