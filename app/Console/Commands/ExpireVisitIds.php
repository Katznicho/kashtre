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
    protected $description = 'Expire visit IDs at midnight by clearing the stored ID and expiry timestamp.';

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

        $clearedCount = 0;

        foreach ($clients as $client) {
            $originalVisitId = $client->visit_id;

            if (empty($originalVisitId) && empty($client->visit_expires_at)) {
                continue;
            }

            $client->visit_id = null;
            $client->visit_expires_at = null;
            $client->save();
            $clearedCount++;

            Log::info('Visit ID cleared for client', [
                'client_id' => $client->id,
                'old_visit_id' => $originalVisitId,
                'reason' => 'Midnight expiration policy',
            ]);
        }

        $this->info("Completed visit ID expiration: cleared {$clearedCount} clients");

        Log::info('=== CRON JOB COMPLETED: ExpireVisitIds ===', [
            'timestamp' => now(),
            'cleared_count' => $clearedCount,
        ]);

        return 0;
    }
}
