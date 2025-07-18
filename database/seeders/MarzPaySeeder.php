<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarzPaySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Business
        $business = Business::create([
            'name' => 'Kashtre',
            'email' => 'katznicho@gmail.com',
            'phone' => '256700000001',
            'address' => 'Kampala, Uganda',
            'logo' => 'logos/marzpay.png',
            'account_number' => 'KS12345678',
        ]);

        // 2. Create the Main Branch
        $branch = Branch::create([
            'uuid' => Str::uuid(),
            'business_id' => $business->id,
            'name' => 'Main Branch',
            'email' => 'main@kashtre.com',
            'phone' => '256700000002',
            'address' => 'Head Office, Kampala',
        ]);

        // 3. Create default Qualifications
        $qualifications = [
            'MBChB',
            'PhD',
            'Diploma in Nursing',
            'Bachelor of Dental Surgery',
            'Master of Public Health',
        ];

        foreach ($qualifications as $name) {
            Qualification::firstOrCreate([
                'business_id' => $business->id,
                'name' => $name,
            ]);
        }

        // 4. Create default Departments
        $departments = [
            'Administration',
            'Finance',
            'Outpatient Department',
            'Laboratory',
            'Pharmacy',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate([
                'business_id' => $business->id,
                'name' => $name,
            ]);
        }

        // 5. Create default Sections
        $sections = [
            'General Medicine',
            'Pediatrics',
            'Surgery',
            'Radiology',
            'Obstetrics & Gynecology',
        ];

        foreach ($sections as $name) {
            Section::firstOrCreate([
                'business_id' => $business->id,
                'name' => $name,
            ]);
        }

        // 6. Create default Titles
        $titles = [
            'Dr.',
            'Mr.',
            'Ms.',
            'Prof.',
            'Eng.',
            'Sr.',
        ];

        foreach ($titles as $name) {
            Title::firstOrCreate([
                'business_id' => $business->id,
                'name' => $name,
            ]);
        }

        // Fetch lookup IDs scoped to this business
        $qualificationId = Qualification::where('business_id', $business->id)->where('name', 'MBChB')->value('id');
        $departmentId    = Department::where('business_id', $business->id)->where('name', 'Administration')->value('id');
        $sectionId       = Section::where('business_id', $business->id)->where('name', 'General Medicine')->value('id');
        $titleId         = Title::where('business_id', $business->id)->where('name', 'Dr.')->value('id');

        // 7. Create Default User (Admin)
        User::create([
            'uuid' => Str::uuid(),
            'name' => 'Kashtre Admin',
            'email' => 'katznicho@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'phone' => '256700000003',
            'nin' => 'CF123456789012',

            'business_id' => $business->id,
            'branch_id' => $branch->id,

            'service_points' => json_encode([]),
            'permissions' => json_encode(['*']),
            'allowed_branches' => json_encode([$branch->id]),

            'qualification_id' => $qualificationId,
            'department_id'    => $departmentId,
            'section_id'       => $sectionId,
            'title_id'         => $titleId,

            'gender' => 'male',
        ]);
    }
}
