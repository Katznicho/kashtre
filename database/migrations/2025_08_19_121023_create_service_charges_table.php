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
        Schema::create('service_charges', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'business', 'branch', 'service_point'
            $table->unsignedBigInteger('entity_id'); // ID of the selected entity
            $table->string('name'); // Service charge name
            $table->decimal('amount', 10, 2); // Amount or percentage
            $table->enum('type', ['fixed', 'percentage']); // Fixed amount or percentage
            $table->text('description')->nullable(); // Optional description
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Indexes
            $table->index(['entity_type', 'entity_id']);
            $table->index('business_id');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_charges');
    }
};
