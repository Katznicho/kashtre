<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

class CleanTestBusinesses extends Command
{
    protected $signature = 'businesses:clean';
    protected $description = 'Clean up test businesses from the database';

    public function handle()
    {
        // Keep the first business (assuming it's the seeded one)
        $preserved = Business::orderBy('created_at', 'asc')->first();

        if ($preserved) {
            // Delete all businesses except the preserved one
            $deleted = Business::where('id', '!=', $preserved->id)->delete();
            $this->info("Cleaned up {$deleted} test businesses. Kept business with ID {$preserved->id}");
        } else {
            $this->info("No businesses found to clean up.");
        }

        return Command::SUCCESS;
    }
}
