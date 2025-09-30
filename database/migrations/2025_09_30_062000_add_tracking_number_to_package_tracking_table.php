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
            $table->string('tracking_number')->nullable()->after('notes');
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_tracking', function (Blueprint $table) {
            $table->dropIndex(['tracking_number']);
            $table->dropColumn('tracking_number');
        });
    }
};
