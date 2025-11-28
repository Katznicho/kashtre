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
        // Modify the action column to be nullable
        DB::statement('ALTER TABLE `credit_limit_change_request_approvals` MODIFY COLUMN `action` ENUM(\'approved\', \'rejected\') NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make the action column NOT NULL again
        // First, set any null values to a default
        DB::statement('UPDATE `credit_limit_change_request_approvals` SET `action` = \'approved\' WHERE `action` IS NULL');
        DB::statement('ALTER TABLE `credit_limit_change_request_approvals` MODIFY COLUMN `action` ENUM(\'approved\', \'rejected\') NOT NULL');
    }
};
