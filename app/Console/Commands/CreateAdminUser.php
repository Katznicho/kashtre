<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {name} {email} {password}';
    protected $description = 'Create a new admin user with business';

    public function handle()
    {
        try {
            // Create business if it doesn't exist
            $business = Business::firstOrCreate(
                ['email' => $this->argument('email')],
                [
                    'name' => 'Kashtre Business',
                    'phone' => '1234567890',
                    'address' => 'Kashtre HQ',
                    'account_number' => 'KSH' . rand(1000, 9999)
                ]
            );

            // Create user
            $user = User::firstOrCreate(
                ['email' => $this->argument('email')],
                [
                    'name' => $this->argument('name'),
                    'password' => Hash::make($this->argument('password')),
                    'business_id' => $business->id
                ]
            );

            $this->info('Admin user created successfully!');
            $this->line('Email: ' . $user->email);
            $this->line('Password: ' . $this->argument('password'));
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
