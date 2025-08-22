<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use App\Models\ContractorProfile;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CityHealthClinicContractorsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating 4 contractors for City Health Clinic...');
        
        // Find City Health Clinic
        $business = Business::where('name', 'LIKE', '%City Health Clinic%')->first();
        
        if (!$business) {
            $this->command->error('City Health Clinic not found!');
            return;
        }
        
        $this->command->info("Found business: {$business->name}");
        
        // Get the main branch
        $mainBranch = $business->branches()->first();
        if (!$mainBranch) {
            $this->command->error('No branches found for City Health Clinic!');
            return;
        }
        
        // Get basic data
        $qualification = $business->qualifications()->first();
        $department = $business->departments()->first();
        $section = $business->sections()->first();
        $title = $business->titles()->first();
        
        // Contractor data
        $contractors = [
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@cityhealthclinic.com',
                'phone' => '256701234567',
                'nin' => 'CT00123456789',
                'gender' => 'female',
                'specialization' => 'General Medicine',
                'bank_name' => 'Stanbic Bank',
                'account_name' => 'Dr. Sarah Johnson',
                'account_number' => '1001234567',
                'kashtre_account_number' => 'KTR001234'
            ],
            [
                'name' => 'Dr. Michael Ochieng',
                'email' => 'michael.ochieng@cityhealthclinic.com',
                'phone' => '256702345678',
                'nin' => 'CT00234567890',
                'gender' => 'male',
                'specialization' => 'Pediatrics',
                'bank_name' => 'Centenary Bank',
                'account_name' => 'Dr. Michael Ochieng',
                'account_number' => '2002345678',
                'kashtre_account_number' => 'KTR002345'
            ],
            [
                'name' => 'Dr. Grace Nakimera',
                'email' => 'grace.nakimera@cityhealthclinic.com',
                'phone' => '256703456789',
                'nin' => 'CT00345678901',
                'gender' => 'female',
                'specialization' => 'Obstetrics & Gynecology',
                'bank_name' => 'DFCU Bank',
                'account_name' => 'Dr. Grace Nakimera',
                'account_number' => '3003456789',
                'kashtre_account_number' => 'KTR003456'
            ],
            [
                'name' => 'Dr. Robert Mugisha',
                'email' => 'robert.mugisha@cityhealthclinic.com',
                'phone' => '256704567890',
                'nin' => 'CT00456789012',
                'gender' => 'male',
                'specialization' => 'Surgery',
                'bank_name' => 'Bank of Uganda',
                'account_name' => 'Dr. Robert Mugisha',
                'account_number' => '4004567890',
                'kashtre_account_number' => 'KTR004567'
            ]
        ];
        
        foreach ($contractors as $index => $contractorData) {
            // Check if contractor already exists
            $existingUser = User::where('email', $contractorData['email'])->first();
            if ($existingUser) {
                $this->command->info("Contractor already exists: {$contractorData['name']}");
                continue;
            }
            
            // Create contractor user
            $user = User::create([
                'uuid' => Str::uuid(),
                'name' => $contractorData['name'],
                'email' => $contractorData['email'],
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone' => $contractorData['phone'],
                'nin' => $contractorData['nin'],
                'business_id' => $business->id,
                'branch_id' => $mainBranch->id,
                'service_points' => [],
                'permissions' => [
                    'View Dashboard',
                    'View Dashboard Cards',
                    'View Dashboard Charts',
                    'Manage Items',
                    'View Reports',
                    'Manage Clients',
                    'Create Invoices',
                    'View Invoices',
                    'Manage Contractor Profile'
                ],
                'allowed_branches' => [$mainBranch->id],
                'qualification_id' => $qualification?->id,
                'department_id' => $department?->id,
                'section_id' => $section?->id,
                'title_id' => $title?->id,
                'gender' => $contractorData['gender'],
            ]);
            
            // Create contractor profile
            ContractorProfile::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $user->id,
                'bank_name' => $contractorData['bank_name'],
                'account_name' => $contractorData['account_name'],
                'account_number' => $contractorData['account_number'],
                'account_balance' => '0',
                'kashtre_account_number' => $contractorData['kashtre_account_number'],
                'signing_qualifications' => $contractorData['specialization']
            ]);
            
            $this->command->info("Created contractor: {$contractorData['name']} ({$contractorData['specialization']})");
        }
        
        $this->command->info('City Health Clinic contractors creation completed!');
    }
}
