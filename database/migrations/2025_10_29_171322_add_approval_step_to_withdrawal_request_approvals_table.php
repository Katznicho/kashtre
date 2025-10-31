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
        Schema::table('withdrawal_request_approvals', function (Blueprint $table) {
            $table->string('approval_step')->nullable()->after('approver_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_request_approvals', function (Blueprint $table) {
            $table->dropColumn('approval_step');
        });
    }
};
