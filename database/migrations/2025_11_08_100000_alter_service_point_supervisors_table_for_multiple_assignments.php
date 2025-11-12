<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_point_supervisors', function (Blueprint $table) {
            $table->dropForeign(['service_point_id']);
            $table->dropUnique('service_point_supervisors_service_point_id_unique');
            $table->unique(['service_point_id', 'supervisor_user_id'], 'service_point_supervisor_unique');
            $table->foreign('service_point_id')->references('id')->on('service_points')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('service_point_supervisors', function (Blueprint $table) {
            $table->dropForeign(['service_point_id']);
            $table->dropUnique('service_point_supervisor_unique');
            $table->unique('service_point_id');
            $table->foreign('service_point_id')->references('id')->on('service_points')->onDelete('cascade');
        });
    }
};

