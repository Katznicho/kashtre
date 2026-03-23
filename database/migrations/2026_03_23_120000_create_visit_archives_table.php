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
        Schema::create('visit_archives', function (Blueprint $table) {
            $table->id();

            // snapshot = DVR at midnight, previous = expired/completed at midnight
            $table->string('record_type', 16);

            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id');

            // Stores one archived row per client per midnight run.
            $table->unsignedBigInteger('client_id');
            $table->string('client_name', 255)->nullable();
            $table->unsignedInteger('client_age')->nullable();

            $table->string('visit_id')->nullable();
            $table->timestamp('archived_at');
            $table->timestamp('visit_end_at')->nullable();

            $table->timestamps();

            $table->unique(['record_type', 'archived_at', 'client_id'], 'visit_archives_type_client_midnight_unique');
            $table->index(['business_id', 'branch_id', 'archived_at'], 'visit_archives_scope_idx');
            $table->index(['record_type', 'archived_at'], 'visit_archives_type_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_archives');
    }
};

