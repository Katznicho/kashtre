<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BusinessBalanceHistory;
use Illuminate\Support\Facades\DB;

class UpdatePendingPaymentsToPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:update-pending-to-paid {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all BusinessBalanceHistory entries with payment_status = "pending_payment" to "paid"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for pending payments...');
        $this->newLine();

        // Count pending payments
        $pendingCount = BusinessBalanceHistory::where('payment_status', 'pending_payment')->count();
        
        if ($pendingCount === 0) {
            $this->info('✅ No pending payments found. All payments are already marked as paid.');
            return 0;
        }

        $this->info("📊 Found {$pendingCount} pending payment(s)");
        $this->newLine();

        // Show breakdown by business
        $this->info('📋 Breakdown by business:');
        $breakdown = BusinessBalanceHistory::where('payment_status', 'pending_payment')
            ->select('business_id', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('business_id')
            ->get();

        foreach ($breakdown as $item) {
            $business = \App\Models\Business::find($item->business_id);
            $businessName = $business ? $business->name : "Business ID: {$item->business_id}";
            $this->line("   • {$businessName}: {$item->count} entries, Total: UGX " . number_format($item->total_amount, 2));
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->info("Would update {$pendingCount} pending payment(s) to 'paid'");
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm('Do you want to update all pending payments to paid?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->newLine();
        $this->info('🔄 Updating pending payments to paid...');

        try {
            DB::beginTransaction();

            $updated = BusinessBalanceHistory::where('payment_status', 'pending_payment')
                ->update(['payment_status' => 'paid']);

            DB::commit();

            $this->newLine();
            $this->info("✅ Successfully updated {$updated} pending payment(s) to 'paid'");

            // Verify
            $remainingPending = BusinessBalanceHistory::where('payment_status', 'pending_payment')->count();
            if ($remainingPending === 0) {
                $this->info('✅ All payments are now marked as paid.');
            } else {
                $this->warn("⚠️  Warning: {$remainingPending} pending payment(s) still remain. Please check the database.");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error occurred: ' . $e->getMessage());
            $this->error('Operation failed. No changes were made.');
            return 1;
        }
    }
}

