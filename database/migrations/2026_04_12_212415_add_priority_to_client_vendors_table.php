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
        Schema::table('client_vendors', function (Blueprint $table) {
            // Cascade order: 1 = first insurer (gets full invoice), 2 = second (gets remainder), etc.
            $table->unsignedTinyInteger('priority')->default(1)->after('is_open_enrollment');
            $table->index(['client_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_vendors', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'priority']);
            $table->dropColumn('priority');
        });
    }
};
