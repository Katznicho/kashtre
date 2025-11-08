<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_point_supervisors', function (Blueprint $table) {
            $table->dropUnique('service_point_supervisors_service_point_id_unique');
            $table->unique(['service_point_id', 'supervisor_user_id'], 'service_point_supervisor_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_point_supervisors', function (Blueprint $table) {
            $table->dropUnique('service_point_supervisor_unique');
            $table->unique('service_point_id');
        });
    }
};

