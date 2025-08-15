<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\User;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;
use App\Models\Item;
use App\Models\Role;
use App\Models\Title;
use App\Models\Qualification;
use App\Models\Room;
use App\Models\Section;
use App\Models\PatientCategory;
use App\Models\Supplier;
use App\Models\InsuranceCompany;
use App\Models\Store;
use App\Models\SubGroup;
use Illuminate\Support\Str;

class AdditionalBusinessesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Additional Businesses Seeding...');
        
        // Check existing businesses to avoid duplicates
        $existingBusinesses = Business::pluck('name')->toArray();
        
        // Define additional businesses to add
        $additionalBusinesses = [
            [
                'name' => 'Fort Portal Regional Hospital',
                'email' => 'info@fortportalhospital.com',
                'phone' => '+256-48-444444',
                'address' => 'Plot 555, Fort Portal Road, Fort Portal, Uganda',
                'account_number' => 'ACC006',
                'date' => now(),
            ],
            [
                'name' => 'Gulu Medical Center',
                'email' => 'contact@gulumedical.com',
                'phone' => '+256-47-555555',
                'address' => 'Plot 666, Gulu Road, Gulu, Uganda',
                'account_number' => 'ACC007',
                'date' => now(),
            ],
            [
                'name' => 'Mbale General Hospital',
                'email' => 'admin@mbalehospital.com',
                'phone' => '+256-45-666666',
                'address' => 'Plot 777, Mbale Road, Mbale, Uganda',
                'account_number' => 'ACC008',
                'date' => now(),
            ],
            [
                'name' => 'Soroti Medical Center',
                'email' => 'info@sorotimedical.com',
                'phone' => '+256-45-777777',
                'address' => 'Plot 888, Soroti Road, Soroti, Uganda',
                'account_number' => 'ACC009',
                'date' => now(),
            ],
            [
                'name' => 'Lira Regional Hospital',
                'email' => 'contact@lirahospital.com',
                'phone' => '+256-47-888888',
                'address' => 'Plot 999, Lira Road, Lira, Uganda',
                'account_number' => 'ACC010',
                'date' => now(),
            ]
        ];

        // Filter out businesses that already exist
        $newBusinesses = array_filter($additionalBusinesses, function($business) use ($existingBusinesses) {
            return !in_array($business['name'], $existingBusinesses);
        });

        if (empty($newBusinesses)) {
            $this->command->info('✅ All additional businesses already exist. No new businesses to add.');
            return;
        }

        $this->command->info('Creating additional businesses...');
        
        foreach ($newBusinesses as $businessData) {
            $business = Business::create($businessData);
            $this->command->info("Created: {$business->name}");
            
            // Create a branch for this business
            $branch = Branch::create([
                'name' => $business->name . ' Main',
                'business_id' => $business->id,
                'email' => 'main@' . strtolower(str_replace(' ', '', $business->name)) . '.com',
                'address' => $business->address,
                'phone' => $business->phone,
            ]);
            
            // Create a user for this business
            $user = User::create([
                'name' => $business->name . ' Admin',
                'email' => 'admin@' . strtolower(str_replace(' ', '', $business->name)) . '.com',
                'password' => Hash::make('password'),
                'business_id' => $business->id,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'Manage Users', 'Manage Items', 'View Reports', 'Manage Settings'])
            ]);
            
            // Create basic groups for this business
            $groups = [
                ['name' => 'Pharmaceuticals', 'business_id' => $business->id],
                ['name' => 'Medical Equipment', 'business_id' => $business->id],
                ['name' => 'Medical Services', 'business_id' => $business->id],
            ];
            
            foreach ($groups as $groupData) {
                Group::create($groupData);
            }
            
            // Create basic departments for this business
            $departments = [
                ['name' => 'Pharmacy', 'business_id' => $business->id],
                ['name' => 'Laboratory', 'business_id' => $business->id],
                ['name' => 'Outpatient', 'business_id' => $business->id],
            ];
            
            foreach ($departments as $deptData) {
                Department::create($deptData);
            }
            
            // Create a service point for this business
            ServicePoint::create([
                'name' => $business->name . ' Reception',
                'business_id' => $business->id,
                'description' => 'Main reception area for ' . $business->name,
            ]);
            
            // Create a store for this business
            Store::create([
                'name' => $business->name . ' Main Store',
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'description' => 'Main storage facility for ' . $business->name,
            ]);
        }

        $this->command->info('✅ Additional businesses seeding completed!');
        $this->command->info('📋 Summary:');
        $this->command->info('   • Added ' . count($newBusinesses) . ' new businesses');
        $this->command->info('   • Created branches, users, groups, departments, service points, and stores for each');
        $this->command->info('');
        $this->command->info('🔑 New Login Credentials:');
        foreach ($newBusinesses as $business) {
            $email = 'admin@' . strtolower(str_replace(' ', '', $business['name'])) . '.com';
            $this->command->info("   • {$business['name']}: {$email} / password");
        }
    }
}
