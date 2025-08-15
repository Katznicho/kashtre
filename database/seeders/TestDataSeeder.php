<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Business;
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

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Test Data Seeding...');

        // Create test businesses
        $this->createTestBusinesses();
        
        // Create test users
        $this->createTestUsers();
        
        // Create test branches
        $this->createTestBranches();
        
        // Create test groups and subgroups
        $this->createTestGroups();
        
        // Create test departments
        $this->createTestDepartments();
        
        // Create test item units
        $this->createTestItemUnits();
        
        // Create test service points
        $this->createTestServicePoints();
        
        // Create test contractors
        $this->createTestContractors();
        
        // Create test items (goods and services)
        $this->createTestItems();
        
        // Create test packages and bulk items
        $this->createTestPackagesAndBulk();
        
        // Create test roles and titles
        $this->createTestRolesAndTitles();
        
        // Create test qualifications
        $this->createTestQualifications();
        
        // Create test rooms and sections
        $this->createTestRoomsAndSections();
        
        // Create test patient categories
        $this->createTestPatientCategories();
        
        // Create test suppliers
        $this->createTestSuppliers();
        
        // Create test insurance companies
        $this->createTestInsuranceCompanies();
        
        // Create test stores
        $this->createTestStores();

        $this->command->info('✅ Test Data Seeding Completed!');
    }

    private function createTestBusinesses()
    {
        $this->command->info('Creating test businesses...');
        
        $businesses = [
            [
                'name' => 'Kampala General Hospital',
                'email' => 'info@kampalahospital.com',
                'phone' => '+256-41-123456',
                'address' => 'Plot 123, Kampala Road, Kampala, Uganda',
                'account_number' => 'ACC001',
                'date' => now(),
            ],
            [
                'name' => 'Nakasero Medical Center',
                'email' => 'contact@nakasero.com',
                'phone' => '+256-41-987654',
                'address' => 'Plot 456, Nakasero Hill, Kampala, Uganda',
                'account_number' => 'ACC002',
                'date' => now(),
            ],
            [
                'name' => 'Jinja Regional Hospital',
                'email' => 'admin@jinjahospital.com',
                'phone' => '+256-43-123456',
                'address' => 'Plot 789, Jinja Road, Jinja, Uganda',
                'account_number' => 'ACC003',
                'date' => now(),
            ],
            [
                'name' => 'Mbarara University Hospital',
                'email' => 'info@mbararahospital.com',
                'phone' => '+256-48-222222',
                'address' => 'Plot 321, Mbarara Road, Mbarara, Uganda',
                'account_number' => 'ACC004',
                'date' => now(),
            ],
            [
                'name' => 'Arua Regional Medical Center',
                'email' => 'contact@aruamedical.com',
                'phone' => '+256-47-333333',
                'address' => 'Plot 654, Arua Road, Arua, Uganda',
                'account_number' => 'ACC005',
                'date' => now(),
            ]
        ];

        foreach ($businesses as $businessData) {
            Business::create($businessData);
        }
    }

    private function createTestUsers()
    {
        $this->command->info('Creating test users...');
        
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'business_id' => 1,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'Manage Users', 'Manage Items', 'View Reports', 'Manage Settings'])
            ],
            [
                'name' => 'Hospital Manager',
                'email' => 'manager@test.com',
                'password' => Hash::make('password'),
                'business_id' => 2,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'Manage Items', 'View Reports'])
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@test.com',
                'password' => Hash::make('password'),
                'business_id' => 1,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'View Items'])
            ],
            [
                'name' => 'Mbarara Admin',
                'email' => 'admin@mbarara.com',
                'password' => Hash::make('password'),
                'business_id' => 4,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'Manage Users', 'Manage Items', 'View Reports', 'Manage Settings'])
            ],
            [
                'name' => 'Arua Manager',
                'email' => 'manager@arua.com',
                'password' => Hash::make('password'),
                'business_id' => 5,
                'email_verified_at' => now(),
                'service_points' => json_encode([]),
                'permissions' => json_encode(['View Dashboard', 'Manage Items', 'View Reports'])
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }

    private function createTestBranches()
    {
        $this->command->info('Creating test branches...');
        
        $branches = [
            ['name' => 'Main Branch', 'business_id' => 1, 'email' => 'main@kampalahospital.com', 'address' => 'Plot 123, Kampala Road', 'phone' => '+256-41-111111'],
            ['name' => 'Nakasero Branch', 'business_id' => 1, 'email' => 'nakasero@kampalahospital.com', 'address' => 'Plot 124, Nakasero Hill', 'phone' => '+256-41-222222'],
            ['name' => 'Entebbe Branch', 'business_id' => 1, 'email' => 'entebbe@kampalahospital.com', 'address' => 'Plot 125, Entebbe Road', 'phone' => '+256-41-333333'],
            ['name' => 'Medical Center Main', 'business_id' => 2, 'email' => 'main@nakasero.com', 'address' => 'Plot 456, Nakasero Hill', 'phone' => '+256-41-444444'],
            ['name' => 'Jinja Main', 'business_id' => 3, 'email' => 'main@jinjahospital.com', 'address' => 'Plot 789, Jinja Road', 'phone' => '+256-43-555555'],
            ['name' => 'Mbarara Main', 'business_id' => 4, 'email' => 'main@mbararahospital.com', 'address' => 'Plot 321, Mbarara Road', 'phone' => '+256-48-666666'],
            ['name' => 'Arua Main', 'business_id' => 5, 'email' => 'main@aruamedical.com', 'address' => 'Plot 654, Arua Road', 'phone' => '+256-47-777777'],
        ];

        foreach ($branches as $branchData) {
            Branch::create($branchData);
        }
    }

    private function createTestGroups()
    {
        $this->command->info('Creating test groups...');
        
        $groups = [
            ['name' => 'Pharmaceuticals', 'business_id' => 1],
            ['name' => 'Medical Equipment', 'business_id' => 1],
            ['name' => 'Laboratory Supplies', 'business_id' => 1],
            ['name' => 'Surgical Instruments', 'business_id' => 1],
            ['name' => 'Medical Services', 'business_id' => 1],
            ['name' => 'Consultation Services', 'business_id' => 1],
            ['name' => 'Diagnostic Services', 'business_id' => 1],
            ['name' => 'Treatment Services', 'business_id' => 1],
        ];

        foreach ($groups as $groupData) {
            Group::create($groupData);
        }

        // Create subgroups
        $subgroups = [
            ['name' => 'Antibiotics', 'business_id' => 1],
            ['name' => 'Painkillers', 'business_id' => 1],
            ['name' => 'Vitamins', 'business_id' => 1],
            ['name' => 'Diagnostic Equipment', 'business_id' => 1],
            ['name' => 'Surgical Tools', 'business_id' => 1],
            ['name' => 'General Consultation', 'business_id' => 1],
            ['name' => 'Specialist Consultation', 'business_id' => 1],
        ];

        foreach ($subgroups as $subgroupData) {
            SubGroup::create($subgroupData);
        }
    }

    private function createTestDepartments()
    {
        $this->command->info('Creating test departments...');
        
        $departments = [
            ['name' => 'Pharmacy', 'business_id' => 1],
            ['name' => 'Laboratory', 'business_id' => 1],
            ['name' => 'Radiology', 'business_id' => 1],
            ['name' => 'Surgery', 'business_id' => 1],
            ['name' => 'Internal Medicine', 'business_id' => 1],
            ['name' => 'Pediatrics', 'business_id' => 1],
            ['name' => 'Emergency', 'business_id' => 1],
            ['name' => 'Outpatient', 'business_id' => 1],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }
    }

    private function createTestItemUnits()
    {
        $this->command->info('Creating test item units...');
        
        $units = [
            ['name' => 'Tablets', 'business_id' => 1],
            ['name' => 'Capsules', 'business_id' => 1],
            ['name' => 'Bottles', 'business_id' => 1],
            ['name' => 'Syringes', 'business_id' => 1],
            ['name' => 'Pieces', 'business_id' => 1],
            ['name' => 'Boxes', 'business_id' => 1],
            ['name' => 'Vials', 'business_id' => 1],
            ['name' => 'Tubes', 'business_id' => 1],
            ['name' => 'Sessions', 'business_id' => 1],
            ['name' => 'Consultations', 'business_id' => 1],
        ];

        foreach ($units as $unitData) {
            ItemUnit::create($unitData);
        }
    }

    private function createTestServicePoints()
    {
        $this->command->info('Creating test service points...');
        
        $servicePoints = [
            ['name' => 'Pharmacy Counter', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Laboratory Reception', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Radiology Department', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Surgery Ward', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Emergency Room', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Outpatient Clinic', 'business_id' => 1, 'branch_id' => 1],
            ['name' => 'Nakasero Pharmacy', 'business_id' => 1, 'branch_id' => 2],
            ['name' => 'Entebbe Lab', 'business_id' => 1, 'branch_id' => 3],
        ];

        foreach ($servicePoints as $servicePointData) {
            ServicePoint::create($servicePointData);
        }
    }

    private function createTestContractors()
    {
        $this->command->info('Creating test contractors...');
        
        // Create contractor users first
        $contractorUsers = [
            [
                'name' => 'Dr. John Smith',
                'email' => 'john.smith@contractor.com',
                'password' => Hash::make('password'),
                'business_id' => 1,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@contractor.com',
                'password' => Hash::make('password'),
                'business_id' => 1,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Michael Brown',
                'email' => 'michael.brown@contractor.com',
                'password' => Hash::make('password'),
                'business_id' => 2,
                'email_verified_at' => now(),
            ]
        ];

        foreach ($contractorUsers as $userData) {
            $user = User::create($userData);
            
            ContractorProfile::create([
                'user_id' => $user->id,
                'business_id' => $user->business_id,
                'account_name' => $user->name,
                'bank_name' => 'Stanbic Bank',
                'account_number' => 'ACC' . rand(100000, 999999),
                'account_balance' => rand(100000, 1000000),
                'kashtre_account_number' => 'KC' . Str::random(10),
                'signing_qualifications' => ['Cardiology', 'Neurology', 'Pediatrics'][rand(0, 2)]
            ]);
        }
    }

    private function createTestItems()
    {
        $this->command->info('Creating test items (goods and services)...');
        
        // Goods
        $goods = [
            [
                'name' => 'Paracetamol 500mg',
                'code' => 'MED001',
                'type' => 'good',
                'description' => 'Pain relief medication',
                'group_id' => 1,
                'subgroup_id' => 2,
                'department_id' => 1,
                'uom_id' => 1,
                'default_price' => 500.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => 'Acetaminophen, Panadol'
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'code' => 'MED002',
                'type' => 'good',
                'description' => 'Antibiotic medication',
                'group_id' => 1,
                'subgroup_id' => 1,
                'department_id' => 1,
                'uom_id' => 1,
                'default_price' => 1200.00,
                'vat_rate' => 18.00,
                'hospital_share' => 80,
                'contractor_account_id' => 1,
                'business_id' => 1,
                'other_names' => 'Amoxil, Trimox'
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'code' => 'MED003',
                'type' => 'good',
                'description' => 'Vitamin supplement',
                'group_id' => 1,
                'subgroup_id' => 3,
                'department_id' => 1,
                'uom_id' => 1,
                'default_price' => 800.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => 'Ascorbic Acid'
            ],
            [
                'name' => 'Syringe 5ml',
                'code' => 'EQUIP001',
                'type' => 'good',
                'description' => 'Disposable syringe',
                'group_id' => 2,
                'subgroup_id' => 4,
                'department_id' => 2,
                'uom_id' => 4,
                'default_price' => 150.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => '5ml Syringe'
            ],
            [
                'name' => 'Blood Test Kit',
                'code' => 'LAB001',
                'type' => 'good',
                'description' => 'Complete blood count test kit',
                'group_id' => 3,
                'department_id' => 2,
                'uom_id' => 5,
                'default_price' => 2500.00,
                'vat_rate' => 18.00,
                'hospital_share' => 90,
                'contractor_account_id' => 2,
                'business_id' => 1,
                'other_names' => 'CBC Kit'
            ]
        ];

        // Services
        $services = [
            [
                'name' => 'General Consultation',
                'code' => 'SVC001',
                'type' => 'service',
                'description' => 'General medical consultation',
                'group_id' => 5,
                'subgroup_id' => 6,
                'department_id' => 8,
                'uom_id' => 9,
                'default_price' => 15000.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => 'GP Consultation'
            ],
            [
                'name' => 'Specialist Consultation',
                'code' => 'SVC002',
                'type' => 'service',
                'description' => 'Specialist medical consultation',
                'group_id' => 5,
                'subgroup_id' => 7,
                'department_id' => 5,
                'uom_id' => 9,
                'default_price' => 25000.00,
                'vat_rate' => 18.00,
                'hospital_share' => 70,
                'contractor_account_id' => 1,
                'business_id' => 1,
                'other_names' => 'Specialist Visit'
            ],
            [
                'name' => 'Blood Test',
                'code' => 'SVC003',
                'type' => 'service',
                'description' => 'Laboratory blood testing',
                'group_id' => 7,
                'department_id' => 2,
                'uom_id' => 9,
                'default_price' => 8000.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => 'Lab Test'
            ],
            [
                'name' => 'X-Ray',
                'code' => 'SVC004',
                'type' => 'service',
                'description' => 'Radiological examination',
                'group_id' => 7,
                'department_id' => 3,
                'uom_id' => 9,
                'default_price' => 12000.00,
                'vat_rate' => 18.00,
                'hospital_share' => 100,
                'business_id' => 1,
                'other_names' => 'Radiography'
            ]
        ];

        foreach ($goods as $itemData) {
            Item::create($itemData);
        }

        foreach ($services as $itemData) {
            Item::create($itemData);
        }
    }

    private function createTestPackagesAndBulk()
    {
        $this->command->info('Creating test packages and bulk items...');
        
        $packages = [
            [
                'name' => 'Basic Health Checkup Package',
                'code' => 'PKG001',
                'type' => 'package',
                'description' => 'Complete basic health screening package',
                'default_price' => 50000.00,
                'vat_rate' => 18.00,
                'validity_days' => 30,
                'business_id' => 1,
                'other_names' => 'Basic Screening Package'
            ],
            [
                'name' => 'Premium Health Package',
                'code' => 'PKG002',
                'type' => 'package',
                'description' => 'Comprehensive health screening with specialist consultation',
                'default_price' => 100000.00,
                'vat_rate' => 18.00,
                'validity_days' => 60,
                'business_id' => 1,
                'other_names' => 'Premium Screening Package'
            ]
        ];

        $bulkItems = [
            [
                'name' => 'Emergency Medical Kit',
                'code' => 'BULK001',
                'type' => 'bulk',
                'description' => 'Complete emergency medical kit with multiple items',
                'default_price' => 25000.00,
                'vat_rate' => 18.00,
                'business_id' => 1,
                'other_names' => 'Emergency Kit'
            ]
        ];

        foreach ($packages as $packageData) {
            Item::create($packageData);
        }

        foreach ($bulkItems as $bulkData) {
            Item::create($bulkData);
        }
    }

    private function createTestRolesAndTitles()
    {
        $this->command->info('Creating test roles and titles...');
        
        $roles = [
            ['name' => 'Administrator', 'business_id' => 1],
            ['name' => 'Manager', 'business_id' => 1],
            ['name' => 'Staff', 'business_id' => 1],
            ['name' => 'Doctor', 'business_id' => 1],
            ['name' => 'Nurse', 'business_id' => 1],
            ['name' => 'Pharmacist', 'business_id' => 1],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        $titles = [
            ['name' => 'Dr.', 'business_id' => 1],
            ['name' => 'Mr.', 'business_id' => 1],
            ['name' => 'Mrs.', 'business_id' => 1],
            ['name' => 'Ms.', 'business_id' => 1],
            ['name' => 'Prof.', 'business_id' => 1],
        ];

        foreach ($titles as $titleData) {
            Title::create($titleData);
        }
    }

    private function createTestQualifications()
    {
        $this->command->info('Creating test qualifications...');
        
        $qualifications = [
            ['name' => 'MBChB', 'business_id' => 1],
            ['name' => 'MD', 'business_id' => 1],
            ['name' => 'PhD', 'business_id' => 1],
            ['name' => 'BSc Nursing', 'business_id' => 1],
            ['name' => 'BPharm', 'business_id' => 1],
            ['name' => 'MSc', 'business_id' => 1],
        ];

        foreach ($qualifications as $qualificationData) {
            Qualification::create($qualificationData);
        }
    }

    private function createTestRoomsAndSections()
    {
        $this->command->info('Creating test rooms and sections...');
        
        $sections = [
            ['name' => 'Ward A', 'business_id' => 1],
            ['name' => 'Ward B', 'business_id' => 1],
            ['name' => 'ICU', 'business_id' => 1],
            ['name' => 'Operating Theater', 'business_id' => 1],
            ['name' => 'Outpatient Clinic', 'business_id' => 1],
        ];

        foreach ($sections as $sectionData) {
            Section::create($sectionData);
        }

        $rooms = [
            ['name' => 'Room 101', 'business_id' => 1],
            ['name' => 'Room 102', 'business_id' => 1],
            ['name' => 'Room 201', 'business_id' => 1],
            ['name' => 'Room 202', 'business_id' => 1],
            ['name' => 'ICU Room 1', 'business_id' => 1],
            ['name' => 'Operating Room 1', 'business_id' => 1],
            ['name' => 'Consultation Room 1', 'business_id' => 1],
        ];

        foreach ($rooms as $roomData) {
            Room::create($roomData);
        }
    }

    private function createTestPatientCategories()
    {
        $this->command->info('Creating test patient categories...');
        
        $categories = [
            ['name' => 'Adult', 'business_id' => 1],
            ['name' => 'Child', 'business_id' => 1],
            ['name' => 'Senior Citizen', 'business_id' => 1],
            ['name' => 'Pregnant Woman', 'business_id' => 1],
            ['name' => 'Emergency Patient', 'business_id' => 1],
        ];

        foreach ($categories as $categoryData) {
            PatientCategory::create($categoryData);
        }
    }

    private function createTestSuppliers()
    {
        $this->command->info('Creating test suppliers...');
        
        $suppliers = [
            [
                'name' => 'MedPharm Uganda Ltd',
                'description' => 'Leading pharmaceutical supplier in Uganda',
                'business_id' => 1
            ],
            [
                'name' => 'Global Medical Supplies',
                'description' => 'International medical equipment supplier',
                'business_id' => 1
            ],
            [
                'name' => 'East Africa Pharmaceuticals',
                'description' => 'Regional pharmaceutical distributor',
                'business_id' => 1
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }

    private function createTestInsuranceCompanies()
    {
        $this->command->info('Creating test insurance companies...');
        
        $insuranceCompanies = [
            [
                'name' => 'AAR Insurance',
                'description' => 'Leading health insurance provider in Uganda',
                'business_id' => 1
            ],
            [
                'name' => 'Jubilee Insurance',
                'description' => 'Comprehensive medical insurance coverage',
                'business_id' => 1
            ],
            [
                'name' => 'UAP Insurance',
                'description' => 'Reliable health insurance solutions',
                'business_id' => 1
            ]
        ];

        foreach ($insuranceCompanies as $insuranceData) {
            InsuranceCompany::create($insuranceData);
        }
    }

    private function createTestStores()
    {
        $this->command->info('Creating test stores...');
        
        $stores = [
            [
                'name' => 'Main Pharmacy Store',
                'business_id' => 1,
                'branch_id' => 1,
                'description' => 'Main pharmacy storage facility'
            ],
            [
                'name' => 'Medical Equipment Store',
                'business_id' => 1,
                'branch_id' => 1,
                'description' => 'Storage for medical equipment and supplies'
            ],
            [
                'name' => 'Laboratory Store',
                'business_id' => 1,
                'branch_id' => 1,
                'description' => 'Storage for laboratory supplies and reagents'
            ]
        ];

        foreach ($stores as $storeData) {
            Store::create($storeData);
        }
    }
}
