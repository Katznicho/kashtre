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
        Schema::table('money_accounts', function (Blueprint $table) {
            $table->enum('status', ['active', 'moved', 'closed'])->default('active')->after('is_active');
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->text('status_notes')->nullable()->after('status_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('money_accounts', function (Blueprint $table) {
            $table->dropColumn(['status', 'status_changed_at', 'status_notes']);
        });
    }
};
