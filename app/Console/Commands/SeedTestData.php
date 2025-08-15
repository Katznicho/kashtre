<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:test-data {--fresh : Run fresh migrations before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with comprehensive test data for development and testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Test Data Seeding Process...');
        
        if ($this->option('fresh')) {
            $this->warn('⚠️  Running fresh migrations...');
            $this->call('migrate:fresh');
        }
        
        $this->info('🌱 Seeding test data...');
        $this->call('db:seed', ['--class' => 'TestDataSeeder']);
        
        $this->info('✅ Test data seeding completed successfully!');
        $this->info('');
        $this->info('📋 Test Data Summary:');
        $this->info('   • 3 Test Businesses');
        $this->info('   • 3 Test Users (admin@test.com, manager@test.com, staff@test.com)');
        $this->info('   • 5 Test Branches');
        $this->info('   • 8 Test Groups with Subgroups');
        $this->info('   • 8 Test Departments');
        $this->info('   • 10 Test Item Units');
        $this->info('   • 8 Test Service Points');
        $this->info('   • 3 Test Contractors');
        $this->info('   • 9 Test Items (5 goods, 4 services)');
        $this->info('   • 3 Test Packages/Bulk Items');
        $this->info('   • 6 Test Roles and 5 Titles');
        $this->info('   • 6 Test Qualifications');
        $this->info('   • 5 Test Sections and 7 Rooms');
        $this->info('   • 5 Test Patient Categories');
        $this->info('   • 3 Test Suppliers');
        $this->info('   • 3 Test Insurance Companies');
        $this->info('   • 3 Test Stores');
        $this->info('');
        $this->info('🔑 Default Login Credentials:');
        $this->info('   Email: admin@test.com | Password: password');
        $this->info('   Email: manager@test.com | Password: password');
        $this->info('   Email: staff@test.com | Password: password');
        $this->info('');
        $this->info('🎯 Ready for testing! You can now test all features with realistic data.');
    }
}
