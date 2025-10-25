<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use App\Models\ContractorProfile;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use App\Models\ServicePoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimpleDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating simple dummy data...');
        
        // Create 3 businesses with only basic fields
        $businesses = [
            [
                'name' => 'Demo Hospital A',
                'email' => 'demoa@example.com',
                'phone' => '256701000001',
                'address' => 'Kampala, Uganda',
                'account_number' => 'DHA12345'
            ],
            [
                'name' => 'Demo Hospital B',
                'email' => 'demob@example.com', 
                'phone' => '256701000002',
                'address' => 'Entebbe, Uganda',
                'account_number' => 'DHB67890'
            ],
            [
                'name' => 'Demo Hospital C',
                'email' => 'democ@example.com',
                'phone' => '256701000003',
                'address' => 'Jinja, Uganda',
                'account_number' => 'DHC11111'
            ]
        ];

        foreach ($businesses as $businessData) {
            // Check if business already exists
            $business = Business::where('email', $businessData['email'])->first();
            if (!$business) {
                $business = Business::create($businessData);
                $this->createBasicDataForBusiness($business);
                $this->command->info("Created business: {$business->name}");
            } else {
                $this->command->info("Business already exists: {$business->name}");
            }
        }

        $this->command->info('Simple dummy data creation completed!');
    }

    private function createBasicDataForBusiness(Business $business)
    {
        // Create 2 branches per business
        for ($i = 1; $i <= 2; $i++) {
            $branch = Branch::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'name' => "Branch $i",
                'email' => "branch$i@" . str_replace([' ', '.'], ['', ''], strtolower($business->name)) . '.com',
                'phone' => '25670' . (1000000 + $business->id * 100 + $i),
                'address' => "Branch $i Location",
            ]);

            // Create 3 service points per branch
            $servicePoints = ['Registration', 'Consultation', 'Pharmacy'];
            foreach ($servicePoints as $name) {
                ServicePoint::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'name' => $name,
                    'description' => $name . ' at ' . $branch->name
                ]);
            }
        }

        // Create basic settings
        $qualifications = ['MBChB', 'Diploma in Nursing', 'BSc Pharmacy'];
        foreach ($qualifications as $name) {
            Qualification::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'name' => $name,
                'description' => 'Qualification: ' . $name
            ]);
        }

        $departments = ['Administration', 'Outpatient', 'Pharmacy'];
        foreach ($departments as $name) {
            Department::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'name' => $name,
                'description' => 'Department: ' . $name
            ]);
        }

        $sections = ['General Medicine', 'Pediatrics', 'Surgery'];
        foreach ($sections as $name) {
            Section::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'name' => $name,
                'description' => 'Section: ' . $name
            ]);
        }

        $titles = ['Dr.', 'Mr.', 'Ms.'];
        foreach ($titles as $name) {
            Title::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'name' => $name,
                'description' => 'Title: ' . $name
            ]);
        }

        // Create users and contractors
        $mainBranch = $business->branches->first();
        $qualification = $business->qualifications()->first();
        $department = $business->departments()->first();
        $section = $business->sections()->first();
        $title = $business->titles()->first();

        // Create admin user
        $admin = User::create([
            'uuid' => Str::uuid(),
            'name' => $business->name . ' Admin',
            'email' => 'admin@' . str_replace([' ', '.'], ['', ''], strtolower($business->name)) . '.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'phone' => '25670' . (2000000 + $business->id * 1000),
            'nin' => 'AD' . str_pad($business->id, 12, '0', STR_PAD_LEFT),
            'business_id' => $business->id,
            'branch_id' => $mainBranch->id,
            'service_points' => [],
            'permissions' => ['View Dashboard'],
            'allowed_branches' => [$mainBranch->id],
            'qualification_id' => $qualification?->id,
            'department_id' => $department?->id,
            'section_id' => $section?->id,
            'title_id' => $title?->id,
            'gender' => 'male',
        ]);

        // Create 2 contractors per business
        for ($i = 1; $i <= 2; $i++) {
            $user = User::create([
                'uuid' => Str::uuid(),
                'name' => "Dr. Contractor $i",
                'email' => "contractor$i@" . str_replace([' ', '.'], ['', ''], strtolower($business->name)) . '.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone' => '25670' . (3000000 + $business->id * 1000 + $i),
                'nin' => 'CT' . str_pad($business->id * 100 + $i, 11, '0', STR_PAD_LEFT),
                'business_id' => $business->id,
                'branch_id' => $mainBranch->id,
                'service_points' => [],
                'permissions' => ['View Dashboard'],
                'allowed_branches' => [$mainBranch->id],
                'qualification_id' => $qualification?->id,
                'department_id' => $department?->id,
                'section_id' => $section?->id,
                'title_id' => $title?->id,
                'gender' => $i == 1 ? 'male' : 'female',
            ]);

            // Create contractor profile (check for existing first)
            $existingProfile = ContractorProfile::where('user_id', $user->id)->first();
            if (!$existingProfile) {
                ContractorProfile::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'user_id' => $user->id,
                    'bank_name' => 'Demo Bank',
                    'account_name' => $user->name,
                    'account_number' => '100' . str_pad($business->id * 100 + $i, 7, '0', STR_PAD_LEFT),
                    'account_balance' => '0',
                    'kashtre_account_number' => 'KTR' . str_pad($business->id * 1000 + $i, 6, '0', STR_PAD_LEFT),
                    'signing_qualifications' => 'General Medicine'
                ]);
            }
        }
    }
}