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
            if (!Schema::hasColumn('withdrawal_request_approvals', 'approver_type')) {
                $table->string('approver_type')->default('user')->after('approver_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_request_approvals', function (Blueprint $table) {
            $table->dropColumn('approver_type');
        });
    }
};
