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
        Schema::create('contractor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->uuid('uuid')->unique()->index();
            $table->string('bank_name')->nullable();         // For fund transfers
            $table->string('account_name')->nullable();      // For fund transfers
            $table->string('account_number')->nullable();
            $table->string("account_balance")->default(0);
            $table->string("kashtre_account_number")->nullable();
            $table->string('signing_qualifications')->nullable();
            $table->softDeletes(); // Allows for soft deletion of contractor profiles
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_profiles');
    }
};
