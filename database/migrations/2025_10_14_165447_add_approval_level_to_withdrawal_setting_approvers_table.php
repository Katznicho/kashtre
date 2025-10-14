<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_setting_approvers', function (Blueprint $table) {
            // Add approval_level field to support 3-level approval system
            $table->enum('approval_level', ['initiator', 'authorizer', 'approver'])->default('approver')->after('approver_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_setting_approvers', function (Blueprint $table) {
            $table->dropColumn('approval_level');
        });
    }
};
