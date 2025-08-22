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
        Schema::create('contractor_service_charges', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('contractor_profile_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('upper_bound', 10, 2)->nullable();
            $table->decimal('lower_bound', 10, 2)->nullable();
            $table->enum('type', ['fixed', 'percentage']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('contractor_profile_id');
            $table->index('business_id');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('contractor_profile_id')->references('id')->on('contractor_profiles')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_service_charges');
    }
};
