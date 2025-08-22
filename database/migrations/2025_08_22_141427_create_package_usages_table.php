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
        Schema::create('package_usages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade')->index();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade')->index();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade')->index(); // Invoice where package was purchased
            $table->foreignId('package_item_id')->constrained('items')->onDelete('cascade')->index(); // The package item
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade')->index(); // The specific item within the package
            $table->integer('quantity_available')->default(1); // How many of this item are available in the package
            $table->integer('quantity_used')->default(0); // How many have been used
            $table->date('purchase_date'); // When the package was purchased
            $table->date('expiry_date'); // When the package expires (purchase_date + validity_days)
            $table->boolean('is_active')->default(true); // Whether the package usage is still valid
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for efficient queries
            $table->index(['client_id', 'included_item_id', 'is_active'], 'package_usages_client_item_active_idx');
            $table->index(['expiry_date', 'is_active'], 'package_usages_expiry_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_usages');
    }
};
