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
        Schema::table('money_transfers', function (Blueprint $table) {
            $table->boolean('money_moved_to_final_account')->default(false)->after('status');
            $table->timestamp('moved_to_final_at')->nullable()->after('money_moved_to_final_account');
            $table->text('final_movement_notes')->nullable()->after('moved_to_final_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('money_transfers', function (Blueprint $table) {
            $table->dropColumn(['money_moved_to_final_account', 'moved_to_final_at', 'final_movement_notes']);
        });
    }
};
