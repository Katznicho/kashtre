<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ServiceDeliveryQueue;
use App\Services\CreditNoteService;
use App\Services\MoneyTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $now = now();
        $extensionDeadline = $now->copy()->subDay();

        Log::info('=== CRON JOB STARTED: ExpireVisitIds ===', [
            'timestamp' => $now,
            'command' => 'visits:expire',
        ]);

        $this->info('Processing visit ID expiration and queue item escalations...');

        $creditNoteService = app(CreditNoteService::class);
        $moneyTrackingService = app(MoneyTrackingService::class);

        $extendedCount = 0;
        $autoNotDoneCount = 0;
        $autoCompletedCount = 0;
        $clientsExtended = collect();
        $clientsMaintained = collect();
        $clearedCount = 0;

        // Step 1: Grant a 24-hour extension to new pending / in-progress items
        $itemsNeedingExtension = ServiceDeliveryQueue::with(['client'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('extended_at')
            ->get();

        foreach ($itemsNeedingExtension as $queueItem) {
            $queueItem->extended_at = $now;
            $queueItem->save();
            $extendedCount++;

            if ($queueItem->client_id) {
                $clientsExtended->push($queueItem->client_id);
            }

            Log::info('Service queue item extended for 24 hours', [
                'queue_id' => $queueItem->id,
                'status' => $queueItem->status,
                'client_id' => $queueItem->client_id,
                'service_point_id' => $queueItem->service_point_id,
                'extended_at' => $queueItem->extended_at,
            ]);
        }

        // Update visit expiry for clients that received an extension
        $uniqueExtendedClientIds = $clientsExtended->filter()->unique();
        if ($uniqueExtendedClientIds->isNotEmpty()) {
            Client::whereIn('id', $uniqueExtendedClientIds)
                ->where(function ($query) {
                    $query->whereNotNull('visit_id')
                        ->orWhereNotNull('visit_expires_at');
                })
                ->chunkById(100, function ($clients) use ($now, &$clientsMaintained) {
                    foreach ($clients as $client) {
                        $client->visit_expires_at = $now->copy()->addDay();
                        $client->save();
                        $clientsMaintained->push($client->id);

                        Log::info('Visit ID expiration extended by 24 hours', [
                            'client_id' => $client->id,
                            'visit_id' => $client->visit_id,
                            'new_expiry' => $client->visit_expires_at,
                        ]);
                    }
                });
        }

        // Step 2: Auto escalate pending items whose extension window elapsed
        $pendingExpired = ServiceDeliveryQueue::with(['client', 'invoice'])
            ->where('status', 'pending')
            ->whereNotNull('extended_at')
            ->where('extended_at', '<=', $extensionDeadline)
            ->get();

        foreach ($pendingExpired as $queueItem) {
            try {
                $queueItem->markAsNotDone();
                $queueItem->extended_at = null;
                $queueItem->save();

                $creditNoteService->initiateRefundWorkflow($queueItem, null, [
                    'trigger' => 'auto_not_done_after_extension',
                    'reason' => 'Service item automatically marked not done after 24-hour extension',
                ]);

                $autoNotDoneCount++;

                Log::info('Queue item auto-marked as not done and refund workflow initiated', [
                    'queue_id' => $queueItem->id,
                    'client_id' => $queueItem->client_id,
                    'invoice_id' => $queueItem->invoice_id,
                    'extended_at' => $queueItem->extended_at,
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to auto-mark queue item as not done', [
                    'queue_id' => $queueItem->id,
                    'client_id' => $queueItem->client_id,
                    'invoice_id' => $queueItem->invoice_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Step 3: Auto complete in-progress items after the extension window
        $inProgressExpired = ServiceDeliveryQueue::with(['client', 'invoice', 'item'])
            ->where('status', 'in_progress')
            ->whereNotNull('extended_at')
            ->where('extended_at', '<=', $extensionDeadline)
            ->get();

        foreach ($inProgressExpired as $queueItem) {
            try {
                $queueItem->markAsCompleted();

                $invoice = $queueItem->invoice;
                if ($invoice) {
                    $itemData = [[
                        'item_id' => $queueItem->item_id,
                        'quantity' => $queueItem->quantity,
                        'total_amount' => ($queueItem->price ?? 0) * ($queueItem->quantity ?? 1),
                    ]];

                    $moneyTrackingService->processSaveAndExit($invoice->fresh(['client', 'business']), $itemData, 'completed');

                    $queueItem->is_money_moved = true;
                    $queueItem->money_moved_at = now();
                    $queueItem->money_moved_by_user_id = null;
                }

                $queueItem->extended_at = null;
                $queueItem->save();

                $autoCompletedCount++;

                Log::info('Queue item auto-completed after extension window', [
                    'queue_id' => $queueItem->id,
                    'client_id' => $queueItem->client_id,
                    'invoice_id' => $queueItem->invoice_id,
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to auto-complete queue item after extension', [
                    'queue_id' => $queueItem->id,
                    'client_id' => $queueItem->client_id,
                    'invoice_id' => $queueItem->invoice_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Step 4: Determine which clients still have active work (pending/in-progress)
        $activeClientIds = ServiceDeliveryQueue::whereIn('status', ['pending', 'in_progress'])
            ->pluck('client_id')
            ->filter()
            ->unique();

        if ($activeClientIds->isNotEmpty()) {
            Client::whereIn('id', $activeClientIds)
                ->where(function ($query) {
                    $query->whereNotNull('visit_id')
                        ->orWhereNotNull('visit_expires_at');
                })
                ->chunkById(100, function ($clients) use ($now, &$clientsMaintained) {
                    foreach ($clients as $client) {
                        $client->visit_expires_at = $now->copy()->addDay();
                        $client->save();
                        $clientsMaintained->push($client->id);
                    }
                });
        }

        $clientsToMaintain = $clientsMaintained->merge($activeClientIds)->unique()->filter();

        // Step 5: Clear visit IDs for all other clients (non-Kashtre)
        // Only clear visit IDs without suffixes (/C, /M, or /C/M) - these are for admitted clients
        Client::where('business_id', '!=', 1)
            ->where(function ($query) {
                $query->whereNotNull('visit_id')
                    ->orWhereNotNull('visit_expires_at');
            })
            ->when($clientsToMaintain->isNotEmpty(), function ($query) use ($clientsToMaintain) {
                $query->whereNotIn('id', $clientsToMaintain);
            })
            ->chunkById(100, function ($clients) use (&$clearedCount) {
                foreach ($clients as $client) {
                    // Only clear visit IDs that don't have suffixes
                    // Visit IDs with /C, /M, or /C/M should not be expired (these are for admitted clients)
                    $hasSuffix = $client->visit_id && preg_match('/\/(C\/M|C|M)$/', $client->visit_id);
                    
                    if (!$hasSuffix) {
                        // Clear visit ID and expiry for clients without suffixes
                        $oldVisitId = $client->visit_id;
                        $client->visit_id = null;
                        $client->visit_expires_at = null;
                        $client->save();
                        $clearedCount++;

                        Log::info('Visit ID expired (no suffix)', [
                            'client_id' => $client->id,
                            'expired_visit_id' => $oldVisitId,
                        ]);
                    } else {
                        // Preserve visit IDs with suffixes (admitted clients)
                        Log::info('Visit ID preserved (has suffix - admitted client)', [
                            'client_id' => $client->id,
                            'preserved_visit_id' => $client->visit_id,
                        ]);
                    }
                }
            });

        $this->info(sprintf(
            'Visit ID job summary: extended=%d, auto_not_done=%d, auto_completed=%d, cleared=%d',
            $extendedCount,
            $autoNotDoneCount,
            $autoCompletedCount,
            $clearedCount
        ));

        Log::info('=== CRON JOB COMPLETED: ExpireVisitIds ===', [
            'timestamp' => now(),
            'extended_items' => $extendedCount,
            'auto_not_done' => $autoNotDoneCount,
            'auto_completed' => $autoCompletedCount,
            'cleared_count' => $clearedCount,
        ]);

        return 0;
    }
}
