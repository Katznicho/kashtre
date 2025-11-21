<?php

namespace App\Console\Commands;

use App\Models\WithdrawalRequest;
use App\Models\WithdrawalSetting;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\MoneyTransfer;
use App\Services\MoneyTrackingService;
use App\Notifications\WithdrawalRequestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoRejectOverdueWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:auto-reject-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject withdrawal requests that have exceeded the maximum approval time and refund the money';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting overdue withdrawal request check...');
        
        $rejectedCount = 0;
        $skippedCount = 0;

        // Get all pending withdrawal requests (not rejected, not approved, not completed)
        $pendingRequests = WithdrawalRequest::whereIn('status', ['pending', 'business_approved', 'kashtre_approved'])
            ->whereNull('rejected_at')
            ->get();

        $this->info("Found {$pendingRequests->count()} pending withdrawal requests to check.");

        foreach ($pendingRequests as $request) {
            try {
                // Get the withdrawal setting for this business and withdrawal type
                $withdrawalSetting = WithdrawalSetting::where('business_id', $request->business_id)
                    ->where('withdrawal_type', $request->withdrawal_type)
                    ->where('is_active', true)
                    ->first();

                // Skip if no setting found or max_approval_time not set
                if (!$withdrawalSetting || !$withdrawalSetting->max_approval_time) {
                    $skippedCount++;
                    continue;
                }

                // Calculate the maximum approval time in hours
                $maxHours = $withdrawalSetting->max_approval_time_unit === 'days' 
                    ? $withdrawalSetting->max_approval_time * 24 
                    : $withdrawalSetting->max_approval_time;

                // Calculate the deadline (created_at + max_approval_time)
                $deadline = $request->created_at->copy()->addHours($maxHours);

                // Check if the deadline has passed
                if (Carbon::now()->greaterThan($deadline)) {
                    $this->info("Rejecting overdue withdrawal request: {$request->uuid} (exceeded by " . Carbon::now()->diffInHours($deadline) . " hours)");

                    DB::beginTransaction();

                    try {
                        // Create rejection record
                        \App\Models\WithdrawalRequestApproval::create([
                            'withdrawal_request_id' => $request->id,
                            'approver_id' => 1, // System auto-rejection
                            'approver_type' => 'system',
                            'approver_level' => 'system',
                            'action' => 'rejected',
                            'comment' => "Automatically rejected due to exceeding maximum approval time of {$withdrawalSetting->max_approval_time} {$withdrawalSetting->max_approval_time_unit}",
                        ]);

                        // Reject the request
                        $request->update([
                            'status' => 'rejected',
                            'rejection_reason' => "Automatically rejected due to exceeding maximum approval time of {$withdrawalSetting->max_approval_time} {$withdrawalSetting->max_approval_time_unit}",
                            'rejected_at' => now(),
                        ]);

                        // Also reject related request if it exists
                        if ($request->relatedRequest) {
                            $request->relatedRequest->update([
                                'status' => 'rejected',
                                'rejection_reason' => 'Related request automatically rejected due to timeout',
                                'rejected_at' => now(),
                            ]);
                        }

                        // No funds to release - withdrawal requests no longer hold funds when created
                        
                        // Notify requester that request was auto-rejected
                        $requester = $request->requester;
                        if ($requester) {
                            $requester->notify(new WithdrawalRequestNotification(
                                $request,
                                'rejected',
                                "Your withdrawal request for " . number_format($request->amount, 2) . " UGX has been automatically rejected due to exceeding the maximum approval time of {$withdrawalSetting->max_approval_time} {$withdrawalSetting->max_approval_time_unit}."
                            ));
                        }

                        Log::info('WR Auto-rejected due to timeout', [
                            'withdrawal_request_id' => $request->id,
                            'withdrawal_request_uuid' => $request->uuid,
                            'business_id' => $request->business_id,
                            'max_approval_time' => $withdrawalSetting->max_approval_time,
                            'max_approval_time_unit' => $withdrawalSetting->max_approval_time_unit,
                            'deadline' => $deadline,
                            'exceeded_by_hours' => Carbon::now()->diffInHours($deadline),
                        ]);

                        $rejectedCount++;
                        DB::commit();
                        $this->info("✓ Auto-rejected withdrawal request: {$request->uuid}");

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Error auto-rejecting overdue withdrawal request', [
                            'withdrawal_request_id' => $request->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $this->error("✗ Failed to auto-reject withdrawal request: {$request->uuid} - {$e->getMessage()}");
                    }
                } else {
                    $skippedCount++;
                }

            } catch (\Exception $e) {
                Log::error('Error processing withdrawal request for timeout check', [
                    'withdrawal_request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("✗ Error processing withdrawal request: {$request->uuid} - {$e->getMessage()}");
                $skippedCount++;
            }
        }

        $this->info("Completed overdue withdrawal check. Auto-rejected: {$rejectedCount}, Skipped: {$skippedCount}");
        
        return Command::SUCCESS;
    }
}
