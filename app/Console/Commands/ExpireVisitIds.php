<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ServiceDeliveryQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireVisitIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visits:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire visit IDs at midnight. Extend visit IDs for clients with pending/in-progress items by 24 hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('=== CRON JOB STARTED: ExpireVisitIds ===', [
            'timestamp' => now(),
            'command' => 'visits:expire',
        ]);

        $this->info('Processing visit ID expiration...');
        
        // Get all clients (excluding Kashtre business_id = 1)
        $clients = Client::where('business_id', '!=', 1)
            ->whereNull('deleted_at')
            ->get();

        $extendedCount = 0;
        $resetCount = 0;
        $midnight = Carbon::today()->startOfDay();
        $nextMidnight = $midnight->copy()->addDay();

        foreach ($clients as $client) {
            // Check if client has pending or in-progress items
            $hasPendingItems = ServiceDeliveryQueue::where('client_id', $client->id)
                ->whereIn('status', ['pending', 'in_progress', 'partially_done'])
                ->exists();

            if ($hasPendingItems) {
                // Extend visit ID for another 24 hours
                $client->visit_expires_at = $nextMidnight;
                $client->save();
                $extendedCount++;
                
                Log::info('Visit ID extended for client', [
                    'client_id' => $client->id,
                    'visit_id' => $client->visit_id,
                    'new_expiry' => $client->visit_expires_at,
                    'reason' => 'Has pending/in-progress items'
                ]);
            } else {
                // Reset visit ID - generate new one
                $business = $client->business;
                $branch = $client->branch;
                
                if ($business && $branch) {
                    $newVisitId = Client::generateVisitId($business, $branch);
                    $client->visit_id = $newVisitId;
                    $client->visit_expires_at = $nextMidnight;
                    $client->save();
                    $resetCount++;
                    
                    Log::info('Visit ID reset for client', [
                        'client_id' => $client->id,
                        'old_visit_id' => $client->getOriginal('visit_id'),
                        'new_visit_id' => $newVisitId,
                        'new_expiry' => $client->visit_expires_at,
                        'reason' => 'No pending/in-progress items'
                    ]);
                }
            }
        }

        $this->info("Completed visit ID expiration:");
        $this->info("  - Extended: {$extendedCount} clients");
        $this->info("  - Reset: {$resetCount} clients");

        Log::info('=== CRON JOB COMPLETED: ExpireVisitIds ===', [
            'timestamp' => now(),
            'extended_count' => $extendedCount,
            'reset_count' => $resetCount,
        ]);

        return 0;
    }
}
