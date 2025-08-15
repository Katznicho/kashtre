<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedAdditionalBusinesses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:additional-businesses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add additional businesses to existing data without affecting current data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Additional Businesses Seeding Process...');
        
        $this->info('🌱 Adding additional businesses to existing data...');
        $this->call('db:seed', ['--class' => 'AdditionalBusinessesSeeder']);
        
        $this->info('✅ Additional businesses seeding completed successfully!');
        $this->info('');
        $this->info('📋 Summary:');
        $this->info('   • Added 5 additional businesses (Fort Portal, Gulu, Mbale, Soroti, Lira)');
        $this->info('   • Created branches, users, groups, departments, service points, and stores for each');
        $this->info('   • Existing data was preserved');
        $this->info('');
        $this->info('🔑 New Login Credentials:');
        $this->info('   • Fort Portal Regional Hospital: admin@fortportalregionalhospital.com / password');
        $this->info('   • Gulu Medical Center: admin@gulumedicalcenter.com / password');
        $this->info('   • Mbale General Hospital: admin@mbalegeneralhospital.com / password');
        $this->info('   • Soroti Medical Center: admin@sorotimedicalcenter.com / password');
        $this->info('   • Lira Regional Hospital: admin@liraregionalhospital.com / password');
    }
}
