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
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->decimal('minimum_withdrawal_amount', 15, 2)->default(500);
            $table->integer('number_of_free_withdrawals_per_day')->default(1);
            $table->integer('min_business_approvers')->default(3);
            $table->integer('min_kashtre_approvers')->default(3);
            $table->enum('withdrawal_type', ['regular', 'express'])->default('regular');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_settings');
    }
};
