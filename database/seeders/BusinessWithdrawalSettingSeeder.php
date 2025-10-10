<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessWithdrawalSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessWithdrawalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first Kashtre user as creator
        $creator = User::where('business_id', 1)->first();
        
        if (!$creator) {
            $this->command->error('No Kashtre user found. Please ensure users exist.');
            return;
        }

        // Get businesses to create settings for
        $kashtre = Business::find(1);
        $businesses = Business::where('id', '!=', 1)->take(3)->get();

        if ($businesses->isEmpty()) {
            $this->command->warn('No other businesses found besides Kashtre.');
        }

        $this->command->info('Creating withdrawal settings for businesses...');

        // 1. Kashtre (business_id = 1) - Premium tier structure
        if ($kashtre) {
            $this->command->info('Creating settings for: ' . $kashtre->name);
            
            $kashtreSettings = [
                [
                    'business_id' => $kashtre->id,
                    'lower_bound' => 0,
                    'upper_bound' => 50000,
                    'charge_amount' => 500,
                    'charge_type' => 'fixed',
                    'description' => 'Standard tier for small withdrawals',
                    'is_active' => true,
                    'created_by' => $creator->id,
                ],
                [
                    'business_id' => $kashtre->id,
                    'lower_bound' => 50001,
                    'upper_bound' => 200000,
                    'charge_amount' => 1000,
                    'charge_type' => 'fixed',
                    'description' => 'Medium tier for moderate withdrawals',
                    'is_active' => true,
                    'created_by' => $creator->id,
                ],
                [
                    'business_id' => $kashtre->id,
                    'lower_bound' => 200001,
                    'upper_bound' => 1000000,
                    'charge_amount' => 2.5,
                    'charge_type' => 'percentage',
                    'description' => 'Percentage-based for large withdrawals',
                    'is_active' => true,
                    'created_by' => $creator->id,
                ],
                [
                    'business_id' => $kashtre->id,
                    'lower_bound' => 1000001,
                    'upper_bound' => 10000000,
                    'charge_amount' => 2,
                    'charge_type' => 'percentage',
                    'description' => 'Premium tier for very large withdrawals',
                    'is_active' => true,
                    'created_by' => $creator->id,
                ],
            ];

            foreach ($kashtreSettings as $setting) {
                BusinessWithdrawalSetting::create($setting);
            }
        }

        // 2. Other businesses - varying structures
        foreach ($businesses as $index => $business) {
            $this->command->info('Creating settings for: ' . $business->name);
            
            if ($index == 0) {
                // First business - Simple flat rate structure
                $settings = [
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 0,
                        'upper_bound' => 100000,
                        'charge_amount' => 1000,
                        'charge_type' => 'fixed',
                        'description' => 'Standard flat rate for all small to medium withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 100001,
                        'upper_bound' => 5000000,
                        'charge_amount' => 3,
                        'charge_type' => 'percentage',
                        'description' => 'Percentage-based for large withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                ];
            } elseif ($index == 1) {
                // Second business - Aggressive pricing
                $settings = [
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 0,
                        'upper_bound' => 30000,
                        'charge_amount' => 300,
                        'charge_type' => 'fixed',
                        'description' => 'Low cost for small withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 30001,
                        'upper_bound' => 150000,
                        'charge_amount' => 800,
                        'charge_type' => 'fixed',
                        'description' => 'Competitive rate for medium withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 150001,
                        'upper_bound' => 500000,
                        'charge_amount' => 1.5,
                        'charge_type' => 'percentage',
                        'description' => 'Percentage for large amounts',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 500001,
                        'upper_bound' => 10000000,
                        'charge_amount' => 1,
                        'charge_type' => 'percentage',
                        'description' => 'Best rate for very large withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                ];
            } else {
                // Third business - Mixed strategy
                $settings = [
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 0,
                        'upper_bound' => 20000,
                        'charge_amount' => 0,
                        'charge_type' => 'fixed',
                        'description' => 'Free withdrawals for very small amounts',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 20001,
                        'upper_bound' => 100000,
                        'charge_amount' => 1200,
                        'charge_type' => 'fixed',
                        'description' => 'Fixed fee for small to medium withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 100001,
                        'upper_bound' => 1000000,
                        'charge_amount' => 2000,
                        'charge_type' => 'fixed',
                        'description' => 'Higher fixed fee for large withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                    [
                        'business_id' => $business->id,
                        'lower_bound' => 1000001,
                        'upper_bound' => 10000000,
                        'charge_amount' => 0.5,
                        'charge_type' => 'percentage',
                        'description' => 'Very competitive percentage for corporate withdrawals',
                        'is_active' => true,
                        'created_by' => $creator->id,
                    ],
                ];
            }

            foreach ($settings as $setting) {
                BusinessWithdrawalSetting::create($setting);
            }
        }

        $totalSettings = BusinessWithdrawalSetting::count();
        $this->command->info("✅ Successfully created {$totalSettings} business withdrawal settings!");
        
        // Display summary
        $this->command->newLine();
        $this->command->info('Summary by Business:');
        $this->command->table(
            ['Business', 'Settings Count', 'Charge Types'],
            Business::withCount('withdrawalSettings')
                ->whereHas('withdrawalSettings')
                ->get()
                ->map(function ($business) {
                    $fixed = $business->withdrawalSettings()->where('charge_type', 'fixed')->count();
                    $percentage = $business->withdrawalSettings()->where('charge_type', 'percentage')->count();
                    return [
                        $business->name,
                        $business->withdrawal_settings_count,
                        "Fixed: {$fixed}, Percentage: {$percentage}"
                    ];
                })
        );
    }
}
