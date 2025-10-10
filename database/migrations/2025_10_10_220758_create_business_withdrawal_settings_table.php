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
        Schema::create('business_withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->decimal('lower_bound', 15, 2)->default(0);
            $table->decimal('upper_bound', 15, 2);
            $table->decimal('charge_amount', 15, 2);
            $table->enum('charge_type', ['fixed', 'percentage'])->default('fixed');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['business_id', 'is_active']);
            $table->index(['lower_bound', 'upper_bound']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_withdrawal_settings');
    }
};
