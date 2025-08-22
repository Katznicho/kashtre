<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Item;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ContractorProfile;
use App\Models\PackageItem;
use App\Models\BulkItem;
use App\Models\BranchItemPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CityHealthClinicItemsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating comprehensive items for City Health Clinic...');
        
        // Find or create City Health Clinic
        $business = Business::where('name', 'LIKE', '%City Health Clinic%')->first();
        
        if (!$business) {
            $this->command->info('City Health Clinic not found, creating it...');
            $business = Business::create([
                'name' => 'City Health Clinic',
                'email' => 'info@cityhealthclinic.com',
                'phone' => '+256700000000',
                'address' => 'Kampala, Uganda',
                'account_number' => 'KS' . time(),
            ]);
            $this->command->info("Created business: {$business->name}");
        }
        
        $this->command->info("Found business: {$business->name}");
        
        // Create basic data if it doesn't exist
        $this->createBasicData($business);
        
        // Get basic data
        $groups = Group::where('business_id', $business->id)->get();
        $departments = Department::where('business_id', $business->id)->get();
        $units = ItemUnit::where('business_id', $business->id)->get();
        $contractors = ContractorProfile::where('business_id', $business->id)->get();
        $branches = $business->branches;
        
        // Get specific groups, departments, and units
        $medicinesGroup = $groups->where('name', 'Medicines')->first();
        $medicalSuppliesGroup = $groups->where('name', 'Medical Supplies')->first();
        $labTestsGroup = $groups->where('name', 'Laboratory Tests')->first();
        $consultationsGroup = $groups->where('name', 'Consultations')->first();
        $proceduresGroup = $groups->where('name', 'Procedures')->first();
        $servicesGroup = $groups->where('name', 'Services')->first();
        
        $pharmacyDept = $departments->where('name', 'Pharmacy')->first();
        $labDept = $departments->where('name', 'Laboratory')->first();
        $outpatientDept = $departments->where('name', 'Outpatient Department')->first();
        $surgeryDept = $departments->where('name', 'Surgery')->first();
        
        $tabletsUnit = $units->where('name', 'Tablets')->first();
        $capsulesUnit = $units->where('name', 'Capsules')->first();
        $bottlesUnit = $units->where('name', 'Bottles')->first();
        $piecesUnit = $units->where('name', 'Pieces')->first();
        $testsUnit = $units->where('name', 'Tests')->first();
        $consultationsUnit = $units->where('name', 'Consultations')->first();
        $proceduresUnit = $units->where('name', 'Procedures')->first();
        
        // Get contractors - use first available ones
        $drSarah = $contractors->first();
        $drMichael = $contractors->skip(1)->first();
        $drGrace = $contractors->skip(2)->first();
        $drRobert = $contractors->skip(3)->first();
        
        $this->command->info('Creating items...');
        
        // ========================================
        // 1. GOOD ITEMS (Hospital Owned)
        // ========================================
        $this->command->info('Creating GOOD items (Hospital owned)...');
        
        $goodItems = [
            // Medicines
            [
                'name' => 'Paracetamol 500mg',
                'type' => 'good',
                'group_id' => $medicinesGroup->id,
                'department_id' => $pharmacyDept->id,
                'uom_id' => $tabletsUnit->id,
                'default_price' => 500,
                'vat_rate' => 18,
                'validity_days' => 365,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Pain relief medication'
            ],
            [
                'name' => 'Amoxicillin 500mg',
                'type' => 'good',
                'group_id' => $medicinesGroup->id,
                'department_id' => $pharmacyDept->id,
                'uom_id' => $capsulesUnit->id,
                'default_price' => 2500,
                'vat_rate' => 18,
                'validity_days' => 365,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Antibiotic medication'
            ],
            [
                'name' => 'Syrup Cough Medicine',
                'type' => 'good',
                'group_id' => $medicinesGroup->id,
                'department_id' => $pharmacyDept->id,
                'uom_id' => $bottlesUnit->id,
                'default_price' => 3500,
                'vat_rate' => 18,
                'validity_days' => 365,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Cough relief syrup'
            ],
            
            // Medical Supplies
            [
                'name' => 'Surgical Gloves',
                'type' => 'good',
                'group_id' => $medicalSuppliesGroup->id,
                'department_id' => $surgeryDept->id,
                'uom_id' => $piecesUnit->id,
                'default_price' => 1500,
                'vat_rate' => 18,
                'validity_days' => 730,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Disposable surgical gloves'
            ],
            [
                'name' => 'Syringes 5ml',
                'type' => 'good',
                'group_id' => $medicalSuppliesGroup->id,
                'department_id' => $pharmacyDept->id,
                'uom_id' => $piecesUnit->id,
                'default_price' => 800,
                'vat_rate' => 18,
                'validity_days' => 730,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Disposable syringes'
            ],
        ];
        
        foreach ($goodItems as $itemData) {
            $this->createItem($business, $itemData);
        }
        
        // ========================================
        // 2. GOOD ITEMS (Contractor Owned)
        // ========================================
        $this->command->info('Creating GOOD items (Contractor owned)...');
        
        $contractorGoodItems = [];
        
        if ($drRobert) {
            $contractorGoodItems[] = [
                'name' => 'Specialized Surgical Instruments',
                'type' => 'good',
                'group_id' => $medicalSuppliesGroup->id,
                'department_id' => $surgeryDept->id,
                'uom_id' => $piecesUnit->id,
                'default_price' => 15000,
                'vat_rate' => 18,
                'validity_days' => 1825,
                'hospital_share' => 30,
                'contractor_account_id' => $drRobert->id,
                'description' => 'Specialized surgical tools owned by Dr. Robert'
            ];
        }
        
        if ($drMichael) {
            $contractorGoodItems[] = [
                'name' => 'Pediatric Equipment Set',
                'type' => 'good',
                'group_id' => $medicalSuppliesGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $piecesUnit->id,
                'default_price' => 25000,
                'vat_rate' => 18,
                'validity_days' => 1825,
                'hospital_share' => 25,
                'contractor_account_id' => $drMichael->id,
                'description' => 'Pediatric equipment owned by Dr. Michael'
            ];
        }
        
        foreach ($contractorGoodItems as $itemData) {
            $this->createItem($business, $itemData);
        }
        
        // ========================================
        // 3. SERVICE ITEMS (Hospital Owned)
        // ========================================
        $this->command->info('Creating SERVICE items (Hospital owned)...');
        
        $serviceItems = [
            [
                'name' => 'General Consultation',
                'type' => 'service',
                'group_id' => $consultationsGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $consultationsUnit->id,
                'default_price' => 15000,
                'vat_rate' => 18,
                'validity_days' => 1,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'General medical consultation'
            ],
            [
                'name' => 'Blood Test - Complete',
                'type' => 'service',
                'group_id' => $labTestsGroup->id,
                'department_id' => $labDept->id,
                'uom_id' => $testsUnit->id,
                'default_price' => 25000,
                'vat_rate' => 18,
                'validity_days' => 7,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Complete blood count test'
            ],
            [
                'name' => 'X-Ray - Chest',
                'type' => 'service',
                'group_id' => $proceduresGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $proceduresUnit->id,
                'default_price' => 35000,
                'vat_rate' => 18,
                'validity_days' => 30,
                'hospital_share' => 100,
                'contractor_account_id' => null,
                'description' => 'Chest X-ray procedure'
            ],
        ];
        
        foreach ($serviceItems as $itemData) {
            $this->createItem($business, $itemData);
        }
        
        // ========================================
        // 4. SERVICE ITEMS (Contractor Owned)
        // ========================================
        $this->command->info('Creating SERVICE items (Contractor owned)...');
        
        $contractorServiceItems = [];
        
        if ($drMichael) {
            $contractorServiceItems[] = [
                'name' => 'Specialist Consultation - Pediatrics',
                'type' => 'service',
                'group_id' => $consultationsGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $consultationsUnit->id,
                'default_price' => 25000,
                'vat_rate' => 18,
                'validity_days' => 1,
                'hospital_share' => 30,
                'contractor_account_id' => $drMichael->id,
                'description' => 'Specialist pediatric consultation by Dr. Michael'
            ];
        }
        
        if ($drRobert) {
            $contractorServiceItems[] = [
                'name' => 'Surgical Procedure - Minor',
                'type' => 'service',
                'group_id' => $proceduresGroup->id,
                'department_id' => $surgeryDept->id,
                'uom_id' => $proceduresUnit->id,
                'default_price' => 150000,
                'vat_rate' => 18,
                'validity_days' => 1,
                'hospital_share' => 40,
                'contractor_account_id' => $drRobert->id,
                'description' => 'Minor surgical procedure by Dr. Robert'
            ];
        }
        
        if ($drGrace) {
            $contractorServiceItems[] = [
                'name' => 'Gynecological Consultation',
                'type' => 'service',
                'group_id' => $consultationsGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $consultationsUnit->id,
                'default_price' => 30000,
                'vat_rate' => 18,
                'validity_days' => 1,
                'hospital_share' => 35,
                'contractor_account_id' => $drGrace->id,
                'description' => 'Gynecological consultation by Dr. Grace'
            ];
        }
        
        foreach ($contractorServiceItems as $itemData) {
            $this->createItem($business, $itemData);
        }
        
        // ========================================
        // 5. BULK ITEMS (Hospital Owned)
        // ========================================
        $this->command->info('Creating BULK items (Hospital owned)...');
        
        // First create the bulk item
        $bulkItem = $this->createItem($business, [
            'name' => 'Emergency Kit - Complete',
            'type' => 'bulk',
            'group_id' => $medicalSuppliesGroup->id,
            'department_id' => $outpatientDept->id,
            'uom_id' => $piecesUnit->id,
            'default_price' => 50000,
            'vat_rate' => 18,
            'validity_days' => 365,
            'hospital_share' => 100,
            'contractor_account_id' => null,
            'description' => 'Complete emergency kit with multiple items'
        ]);
        
        // Get some good items to include in the bulk
        $paracetamol = Item::where('name', 'Paracetamol 500mg')->where('business_id', $business->id)->first();
        $gloves = Item::where('name', 'Surgical Gloves')->where('business_id', $business->id)->first();
        $syringes = Item::where('name', 'Syringes 5ml')->where('business_id', $business->id)->first();
        
        if ($paracetamol && $gloves && $syringes) {
            // Create bulk item constituents
            BulkItem::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'bulk_item_id' => $bulkItem->id,
                'included_item_id' => $paracetamol->id,
                'fixed_quantity' => 10
            ]);
            
            BulkItem::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'bulk_item_id' => $bulkItem->id,
                'included_item_id' => $gloves->id,
                'fixed_quantity' => 5
            ]);
            
            BulkItem::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'bulk_item_id' => $bulkItem->id,
                'included_item_id' => $syringes->id,
                'fixed_quantity' => 3
            ]);
        }
        
        // ========================================
        // 6. PACKAGE ITEMS (Hospital Owned)
        // ========================================
        $this->command->info('Creating PACKAGE items (Hospital owned)...');
        
        // Create a wellness package
        $wellnessPackage = $this->createItem($business, [
            'name' => 'Wellness Checkup Package',
            'type' => 'package',
            'group_id' => $servicesGroup->id,
            'department_id' => $outpatientDept->id,
            'uom_id' => $piecesUnit->id,
            'default_price' => 75000,
            'vat_rate' => 18,
            'validity_days' => 90,
            'hospital_share' => 100,
            'contractor_account_id' => null,
            'description' => 'Complete wellness checkup package with consultation and tests'
        ]);
        
        // Get service items to include in the package
        $generalConsultation = Item::where('name', 'General Consultation')->where('business_id', $business->id)->first();
        $bloodTest = Item::where('name', 'Blood Test - Complete')->where('business_id', $business->id)->first();
        
        if ($generalConsultation && $bloodTest) {
            // Create package item constituents
            PackageItem::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'package_item_id' => $wellnessPackage->id,
                'included_item_id' => $generalConsultation->id,
                'max_quantity' => 2
            ]);
            
            PackageItem::create([
                'uuid' => Str::uuid(),
                'business_id' => $business->id,
                'package_item_id' => $wellnessPackage->id,
                'included_item_id' => $bloodTest->id,
                'max_quantity' => 1
            ]);
        }
        
        // ========================================
        // 7. PACKAGE ITEMS (Contractor Owned)
        // ========================================
        $this->command->info('Creating PACKAGE items (Contractor owned)...');
        
        // Create a specialist package only if we have contractors
        if ($drSarah) {
            $specialistPackage = $this->createItem($business, [
                'name' => 'Specialist Care Package',
                'type' => 'package',
                'group_id' => $servicesGroup->id,
                'department_id' => $outpatientDept->id,
                'uom_id' => $piecesUnit->id,
                'default_price' => 120000,
                'vat_rate' => 18,
                'validity_days' => 180,
                'hospital_share' => 40,
                'contractor_account_id' => $drSarah->id,
                'description' => 'Specialist care package with multiple consultations'
            ]);
            
            // Get contractor service items
            $pediatricConsultation = Item::where('name', 'Specialist Consultation - Pediatrics')->where('business_id', $business->id)->first();
            
            if ($pediatricConsultation) {
                PackageItem::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'package_item_id' => $specialistPackage->id,
                    'included_item_id' => $pediatricConsultation->id,
                    'max_quantity' => 3
                ]);
            }
        }
        
        // ========================================
        // 8. CREATE BRANCH PRICES FOR ALL ITEMS
        // ========================================
        $this->command->info('Creating branch prices for all items...');
        
        $items = Item::where('business_id', $business->id)->get();
        
        foreach ($items as $item) {
            foreach ($branches as $branch) {
                // Add some price variation between branches
                $priceVariation = rand(-10, 10) / 100; // ±10% variation
                $branchPrice = $item->default_price * (1 + $priceVariation);
                
                BranchItemPrice::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'item_id' => $item->id,
                    'price' => round($branchPrice, 2)
                ]);
            }
        }
        
        $this->command->info('City Health Clinic items creation completed!');
        $this->command->info('Created ' . $items->count() . ' items with branch pricing.');
    }
    
    private function createItem($business, $itemData)
    {
        $item = Item::create(array_merge($itemData, [
            'business_id' => $business->id,
            'other_names' => null
        ]));
        
        $this->command->info("Created item: {$item->name} ({$item->type})" . 
            ($item->contractor_account_id ? " - Contractor Item" : " - Hospital Item"));
        
        return $item;
    }
    
    private function createBasicData($business)
    {
        // Create groups if they don't exist
        $groups = [
            'Medicines', 'Medical Supplies', 'Laboratory Tests', 
            'Consultations', 'Procedures', 'Services'
        ];
        
        foreach ($groups as $groupName) {
            if (!Group::where('name', $groupName)->where('business_id', $business->id)->exists()) {
                Group::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'name' => $groupName,
                    'description' => $groupName . ' for ' . $business->name
                ]);
                $this->command->info("Created group: {$groupName}");
            }
        }
        
        // Create departments if they don't exist
        $departments = [
            'Pharmacy', 'Laboratory', 'Outpatient Department', 'Surgery'
        ];
        
        foreach ($departments as $deptName) {
            if (!Department::where('name', $deptName)->where('business_id', $business->id)->exists()) {
                Department::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'name' => $deptName,
                    'description' => $deptName . ' for ' . $business->name
                ]);
                $this->command->info("Created department: {$deptName}");
            }
        }
        
        // Create units if they don't exist
        $units = [
            'Tablets', 'Capsules', 'Bottles', 'Pieces', 'Tests', 'Consultations', 'Procedures'
        ];
        
        foreach ($units as $unitName) {
            if (!ItemUnit::where('name', $unitName)->where('business_id', $business->id)->exists()) {
                ItemUnit::create([
                    'uuid' => Str::uuid(),
                    'business_id' => $business->id,
                    'name' => $unitName,
                    'description' => $unitName . ' unit for ' . $business->name
                ]);
                $this->command->info("Created unit: {$unitName}");
            }
        }
        
        // Create a default branch if none exists
        if ($business->branches->isEmpty()) {
            $branch = $business->branches()->create([
                'uuid' => Str::uuid(),
                'name' => 'Main Branch',
                'email' => 'main@cityhealthclinic.com',
                'phone' => '+256700000001',
                'address' => 'Kampala, Uganda'
            ]);
            $this->command->info("Created branch: {$branch->name}");
        }
    }
}
