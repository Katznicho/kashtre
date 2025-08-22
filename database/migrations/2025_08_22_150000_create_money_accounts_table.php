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
        Schema::create('money_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name'); // Account name (e.g., "Client Account", "Package Suspense Account")
            $table->enum('type', [
                'client_account',
                'package_suspense_account', 
                'general_suspense_account',
                'kashtre_suspense_account',
                'business_account',
                'contractor_account',
                'kashtre_account'
            ]);
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade'); // For client accounts
            $table->foreignId('contractor_profile_id')->nullable()->constrained()->onDelete('cascade'); // For contractor accounts
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency')->default('UGX');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('money_accounts');
    }
};
