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
            $table->boolean('is_finalized')->default(false)->after('status');
            $table->timestamp('finalized_at')->nullable()->after('is_finalized');
            $table->unsignedBigInteger('finalized_by_user_id')->nullable()->after('finalized_at');
            $table->foreign('finalized_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            $table->dropForeign(['finalized_by_user_id']);
            $table->dropColumn(['is_finalized', 'finalized_at', 'finalized_by_user_id']);
        });
    }
};
