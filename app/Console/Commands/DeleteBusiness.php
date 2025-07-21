<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

class DeleteBusiness extends Command
{
    protected $signature = 'business:delete {email} {phone?}';
    protected $description = 'Delete a business by email (and optionally phone)';

    public function handle()
    {
        $email = $this->argument('email');
        $phone = $this->argument('phone');

        $query = Business::where('email', $email);
        if ($phone) {
            $query->where('phone', $phone);
        }

        $deleted = $query->delete();

        if ($deleted) {
            $this->info("Successfully deleted business with email: {$email}");
        } else {
            $this->error("No business found with email: {$email}");
        }

        return Command::SUCCESS;
    }
}
