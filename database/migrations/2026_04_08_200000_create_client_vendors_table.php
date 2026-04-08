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
        Schema::create('client_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('third_party_payer_id');
            $table->string('policy_number')->nullable();
            $table->boolean('policy_verified')->default(false);
            $table->boolean('is_open_enrollment')->default(false);
            $table->decimal('deductible_amount', 12, 2)->nullable();
            $table->decimal('copay_amount', 12, 2)->nullable();
            $table->decimal('coinsurance_percentage', 5, 2)->nullable();
            $table->decimal('copay_max_limit', 12, 2)->nullable();
            $table->boolean('copay_contributes_to_deductible')->default(false);
            $table->boolean('coinsurance_contributes_to_deductible')->default(false);
            $table->json('excluded_items')->nullable();
            $table->string('status')->default('active'); // active, suspended, blocked
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');

            $table->foreign('third_party_payer_id')
                ->references('id')
                ->on('third_party_payers')
                ->onDelete('cascade');

            $table->unique(['client_id', 'third_party_payer_id']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_vendors');
    }
};
