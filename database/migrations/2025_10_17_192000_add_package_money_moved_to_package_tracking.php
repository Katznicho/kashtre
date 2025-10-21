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
        Schema::table('package_tracking', function (Blueprint $table) {
            $table->boolean('package_money_moved')->default(false)->after('status');
            $table->timestamp('money_moved_at')->nullable()->after('package_money_moved');
            $table->text('money_movement_notes')->nullable()->after('money_moved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_tracking', function (Blueprint $table) {
            $table->dropColumn(['package_money_moved', 'money_moved_at', 'money_movement_notes']);
        });
    }
};
