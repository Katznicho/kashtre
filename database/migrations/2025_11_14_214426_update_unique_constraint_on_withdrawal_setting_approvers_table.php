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
        // Use raw SQL to handle foreign key constraints properly
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::table('withdrawal_setting_approvers', function (Blueprint $table) {
            // Drop the old unique constraint that doesn't include approval_level
            // Note: This might be used by a foreign key, so we disable FK checks above
            $table->dropUnique('unique_approver_per_setting_level');
            
            // Add new unique constraint that includes approval_level
            // This allows the same approver to have different approval levels (initiator, authorizer, approver)
            // but prevents duplicates with the same combination
            $table->unique(
                ['withdrawal_setting_id', 'approver_id', 'approver_type', 'approver_level', 'approval_level'],
                'unique_approver_per_setting_level_and_approval_level'
            );
        });
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_setting_approvers', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('unique_approver_per_setting_level_and_approval_level');
            
            // Restore the old constraint (without approval_level)
            $table->unique(
                ['withdrawal_setting_id', 'approver_id', 'approver_type', 'approver_level'],
                'unique_approver_per_setting_level'
            );
        });
    }
};
