<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage VARCHAR(20)");

        DB::table('credit_note_approvals')
            ->where('stage', 'finance')
            ->update(['stage' => 'authorizer']);

        DB::table('credit_note_approvals')
            ->where('stage', 'ceo')
            ->update(['stage' => 'approver']);

        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage ENUM('supervisor','authorizer','approver')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage VARCHAR(20)");

        DB::table('credit_note_approvals')
            ->where('stage', 'authorizer')
            ->update(['stage' => 'finance']);

        DB::table('credit_note_approvals')
            ->where('stage', 'approver')
            ->update(['stage' => 'ceo']);

        DB::statement("ALTER TABLE credit_note_approvals MODIFY stage ENUM('supervisor','finance','ceo')");
    }
};

