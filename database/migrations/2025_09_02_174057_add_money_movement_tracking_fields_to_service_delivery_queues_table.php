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
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            $table->boolean('is_money_moved')->default(false)->after('started_by_user_id');
            $table->timestamp('money_moved_at')->nullable()->after('is_money_moved');
            $table->unsignedBigInteger('money_moved_by_user_id')->nullable()->after('money_moved_at');
            
            $table->foreign('money_moved_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            $table->dropForeign(['money_moved_by_user_id']);
            $table->dropColumn(['is_money_moved', 'money_moved_at', 'money_moved_by_user_id']);
        });
    }
};
