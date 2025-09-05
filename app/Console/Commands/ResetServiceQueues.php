<?php

namespace App\Console\Commands;

use App\Models\ServiceDeliveryQueue;
use App\Models\ServicePoint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetServiceQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-queues:reset 
                            {--service-point= : Reset queues for specific service point ID}
                            {--all : Reset all service point queues}
                            {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset service delivery queues for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $servicePointId = $this->option('service-point');
        $resetAll = $this->option('all');
        $force = $this->option('force');

        if (!$servicePointId && !$resetAll) {
            $this->error('Please specify either --service-point=ID or --all option');
            return 1;
        }

        if ($servicePointId && $resetAll) {
            $this->error('Cannot use both --service-point and --all options');
            return 1;
        }

        if ($servicePointId) {
            $this->resetSpecificServicePoint($servicePointId, $force);
        } elseif ($resetAll) {
            $this->resetAllServicePoints($force);
        }

        return 0;
    }

    /**
     * Reset queues for a specific service point
     */
    private function resetSpecificServicePoint($servicePointId, $force)
    {
        $servicePoint = ServicePoint::find($servicePointId);
        
        if (!$servicePoint) {
            $this->error("Service point with ID {$servicePointId} not found");
            return;
        }

        $pendingCount = ServiceDeliveryQueue::where('service_point_id', $servicePointId)
            ->whereIn('status', ['pending', 'in_progress', 'partially_done'])
            ->count();

        if ($pendingCount === 0) {
            $this->info("No pending items found for service point: {$servicePoint->name}");
            return;
        }

        if (!$force) {
            if (!$this->confirm("Are you sure you want to reset {$pendingCount} queued items for service point '{$servicePoint->name}'?")) {
                $this->info('Operation cancelled');
                return;
            }
        }

        $this->info("Resetting {$pendingCount} queued items for service point: {$servicePoint->name}");

        ServiceDeliveryQueue::where('service_point_id', $servicePointId)
            ->whereIn('status', ['pending', 'in_progress', 'partially_done'])
            ->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

        $this->info("Successfully reset {$pendingCount} queued items for {$servicePoint->name}");
        
        Log::info("Service queues reset for service point {$servicePoint->name} by command line", [
            'service_point_id' => $servicePointId,
            'items_reset' => $pendingCount
        ]);
    }

    /**
     * Reset all service point queues
     */
    private function resetAllServicePoints($force)
    {
        $pendingCount = ServiceDeliveryQueue::whereIn('status', ['pending', 'in_progress', 'partially_done'])
            ->count();

        if ($pendingCount === 0) {
            $this->info('No pending items found across all service points');
            return;
        }

        if (!$force) {
            if (!$this->confirm("Are you sure you want to reset {$pendingCount} queued items across ALL service points?")) {
                $this->info('Operation cancelled');
                return;
            }
        }

        $this->info("Resetting {$pendingCount} queued items across all service points");

        ServiceDeliveryQueue::whereIn('status', ['pending', 'in_progress', 'partially_done'])
            ->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

        $this->info("Successfully reset {$pendingCount} queued items across all service points");
        
        Log::info("All service queues reset by command line", [
            'items_reset' => $pendingCount
        ]);
    }
}


