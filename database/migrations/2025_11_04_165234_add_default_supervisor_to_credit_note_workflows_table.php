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
        Schema::table('credit_note_workflows', function (Blueprint $table) {
            $table->foreignId('default_supervisor_user_id')->nullable()->after('business_id')->constrained('users')->onDelete('set null')->comment('Default Technical Supervisor for all service points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_note_workflows', function (Blueprint $table) {
            $table->dropForeign(['default_supervisor_user_id']);
            $table->dropColumn('default_supervisor_user_id');
        });
    }
};
