<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Console\Command;

class SetupUserAssociations extends Command
{
    protected $signature = 'user:setup-associations {email}';
    protected $description = 'Set up business and branch associations for a user';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return;
        }

        // Create or get business
        $business = Business::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test Business',
                'phone' => '1234567890',
                'address' => 'Test Address',
                'account_number' => 'TEST' . rand(1000, 9999)
            ]
        );

        // Create or get branch
        $branch = Branch::firstOrCreate(
            ['business_id' => $business->id],
            [
                'name' => 'Main Branch',
                'email' => 'branch@' . $business->name,
                'phone' => '1234567890',
                'address' => 'Branch Address'
            ]
        );

        // Update user
        $user->update([
            'business_id' => $business->id,
            'branch_id' => $branch->id
        ]);

        $this->info('User associations set up successfully!');
        $this->line("Business: {$business->name}");
        $this->line("Branch: {$branch->name}");
    }
}
