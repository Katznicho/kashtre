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
            $table->unsignedBigInteger('started_by_user_id')->nullable()->after('partially_done_at');
            $table->foreign('started_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            $table->dropForeign(['started_by_user_id']);
            $table->dropColumn('started_by_user_id');
        });
    }
};
