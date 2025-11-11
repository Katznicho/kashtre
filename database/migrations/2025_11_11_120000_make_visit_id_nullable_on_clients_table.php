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
        DB::statement('ALTER TABLE clients MODIFY visit_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clients MODIFY visit_expires_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE clients MODIFY visit_id VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE clients MODIFY visit_expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
};

