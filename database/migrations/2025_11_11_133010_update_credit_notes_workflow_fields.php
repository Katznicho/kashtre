<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('current_stage')->nullable()->after('status');
            $table->foreignId('initiated_by')->nullable()->after('processed_by')->constrained('users')->nullOnDelete();
        });

        DB::statement("ALTER TABLE credit_notes MODIFY status ENUM('pending','approved','processed','cancelled') DEFAULT 'pending'");
        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage ENUM('supervisor','authorizer','approver')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE credit_notes MODIFY status ENUM('pending','approved','processed','cancelled') DEFAULT 'approved'");

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('current_stage');
            $table->dropForeign(['initiated_by']);
            $table->dropColumn('initiated_by');
        });

        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage ENUM('supervisor','finance','ceo')");
    }
};

