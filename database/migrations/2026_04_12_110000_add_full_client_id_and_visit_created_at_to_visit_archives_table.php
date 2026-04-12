<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_archives', function (Blueprint $table) {
            $table->string('full_client_id')->nullable()->after('client_id');
            $table->timestamp('visit_created_at')->nullable()->after('visit_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('visit_archives', function (Blueprint $table) {
            $table->dropColumn(['full_client_id', 'visit_created_at']);
        });
    }
};
