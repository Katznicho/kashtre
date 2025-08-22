<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Services\MoneyTrackingService;

class MoneyAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moneyTrackingService = new MoneyTrackingService();
        
        // Get all businesses
        $businesses = Business::all();
        
        foreach ($businesses as $business) {
            $this->command->info("Initializing money accounts for business: {$business->name}");
            
            try {
                $moneyTrackingService->initializeBusinessAccounts($business);
                $this->command->info("✓ Money accounts initialized for {$business->name}");
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to initialize money accounts for {$business->name}: {$e->getMessage()}");
            }
        }
        
        $this->command->info("Money accounts initialization completed!");
    }
}
