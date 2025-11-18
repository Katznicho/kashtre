<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to handle constraints properly
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop old unique constraints using raw SQL
        try {
            DB::statement('ALTER TABLE `withdrawal_request_approvals` DROP INDEX `wr_approval_unique`');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE `withdrawal_request_approvals` DROP INDEX `unique_withdrawal_approval`');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }
        
        // Add new unique constraint that includes approval_step and approver_level
        // This allows the same user to approve at different steps but prevents duplicates at the same step
        DB::statement('ALTER TABLE `withdrawal_request_approvals` ADD UNIQUE INDEX `wr_approval_unique_with_step` (`withdrawal_request_id`, `approver_id`, `approval_step`, `approver_level`)');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop new constraint
        try {
            DB::statement('ALTER TABLE `withdrawal_request_approvals` DROP INDEX `wr_approval_unique_with_step`');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }
        
        // Restore the old constraint (without approval_step and approver_level)
        DB::statement('ALTER TABLE `withdrawal_request_approvals` ADD UNIQUE INDEX `wr_approval_unique` (`withdrawal_request_id`, `approver_id`)');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
