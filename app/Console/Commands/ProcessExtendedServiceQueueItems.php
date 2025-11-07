<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceDeliveryQueue;
use App\Models\CreditNote;
use App\Models\BalanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessExtendedServiceQueueItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-queue:process-extended-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process service queue items that have passed their 24-hour extension period: mark in-progress items as completed, mark pending items as not done (credit note creation is commented out)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing extended service queue items...');

        // Find items where extended_at + 24 hours < now() and status is partially_done or pending
        $cutoffTime = Carbon::now()->subHours(24);
        
        $extendedItems = ServiceDeliveryQueue::whereNotNull('extended_at')
            ->where('extended_at', '<=', $cutoffTime)
            ->whereIn('status', ['partially_done', 'pending'])
            ->with(['invoice', 'client', 'item'])
            ->get();

        if ($extendedItems->isEmpty()) {
            $this->info('No extended items found to process.');
            return 0;
        }

        $this->info("Found {$extendedItems->count()} extended items to process.");

        $partiallyDoneCount = 0;
        $pendingCount = 0;
        $creditNotesCreated = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($extendedItems as $item) {
                try {
                    if ($item->status === 'partially_done') {
                        // Mark as completed
                        $item->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        $partiallyDoneCount++;
                        
                        $this->info("Marked item #{$item->id} ({$item->item_name}) as completed.");
                    } elseif ($item->status === 'pending') {
                        // Mark as not_done
                        $item->update([
                            'status' => 'not_done',
                        ]);

                        // TODO: Credit note creation commented out - to be implemented later
                        // Calculate credit amount (item price * quantity)
                        // $creditAmount = $item->price * $item->quantity;

                        // Create credit note
                        // $creditNote = CreditNote::create([
                        //     'service_delivery_queue_id' => $item->id,
                        //     'invoice_id' => $item->invoice_id,
                        //     'client_id' => $item->client_id,
                        //     'business_id' => $item->business_id,
                        //     'branch_id' => $item->branch_id,
                        //     'amount' => $creditAmount,
                        //     'reason' => "Service not completed within 24-hour extension period - Item: {$item->item_name}",
                        //     'status' => 'approved',
                        //     'processed_at' => now(),
                        // ]);

                        // Credit the client's account balance
                        // if ($item->client) {
                        //     BalanceHistory::recordCredit(
                        //         $item->client,
                        //         $creditAmount,
                        //         "Credit Note #{$creditNote->credit_note_number} - {$item->item_name} (Service not completed)",
                        //         $creditNote->credit_note_number,
                        //         "Automatic credit note for incomplete service after 24-hour extension period"
                        //     );

                        //     // Update client balance
                        //     $item->client->increment('balance', $creditAmount);
                        // }

                        $pendingCount++;
                        // $creditNotesCreated++;

                        $this->info("Marked item #{$item->id} ({$item->item_name}) as not done. (Credit note creation is commented out)");
                        // $this->info("Marked item #{$item->id} ({$item->item_name}) as not done and created credit note #{$creditNote->credit_note_number} for UGX " . number_format($creditAmount, 2));
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Failed to process extended service queue item #{$item->id}", [
                        'item_id' => $item->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $this->error("Error processing item #{$item->id}: {$e->getMessage()}");
                }
            }

            DB::commit();

            $this->info("\nProcessing complete!");
            $this->info("In-progress items marked as completed: {$partiallyDoneCount}");
            $this->info("Pending items marked as not done: {$pendingCount}");
            // $this->info("Credit notes created: {$creditNotesCreated}"); // Commented out - credit note creation disabled
            
            if ($errors > 0) {
                $this->warn("Errors encountered: {$errors}");
            }

            Log::info("Extended service queue items processed", [
                'partially_done_completed' => $partiallyDoneCount,
                'pending_not_done' => $pendingCount,
                // 'credit_notes_created' => $creditNotesCreated, // Commented out - credit note creation disabled
                'errors' => $errors
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process extended service queue items", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error("Fatal error: {$e->getMessage()}");
            return 1;
        }
    }
}

