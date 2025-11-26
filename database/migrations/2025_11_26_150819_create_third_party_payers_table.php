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
        Schema::create('third_party_payers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['insurance_company', 'normal_client'])->default('insurance_company');
            $table->foreignId('insurance_company_id')->nullable()->constrained('insurance_companies')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00)->comment('Maximum credit limit for this third party payer');
            $table->decimal('current_balance', 15, 2)->default(0.00)->comment('Current outstanding balance');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ensure only one of insurance_company_id or client_id is set based on type
            $table->index(['business_id', 'type']);
            $table->index(['insurance_company_id']);
            $table->index(['client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_payers');
    }
};
