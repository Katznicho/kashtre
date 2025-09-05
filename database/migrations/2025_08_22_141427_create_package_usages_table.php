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
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('invoice_id')->index(); // Invoice where package was purchased
            $table->unsignedBigInteger('package_item_id')->index(); // The package item
            $table->unsignedBigInteger('included_item_id')->index(); // The specific item within the package
            $table->integer('quantity_available')->default(1); // How many of this item are available in the package
            $table->integer('quantity_used')->default(0); // How many have been used
            $table->date('purchase_date'); // When the package was purchased
            $table->date('expiry_date'); // When the package expires (purchase_date + validity_days)
            $table->boolean('is_active')->default(true); // Whether the package usage is still valid
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints with explicit names
            $table->foreign('client_id', 'package_usages_client_id_fk')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('business_id', 'package_usages_business_id_fk')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('invoice_id', 'package_usages_invoice_id_fk')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('package_item_id', 'package_usages_package_item_id_fk')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('included_item_id', 'package_usages_included_item_id_fk')->references('id')->on('items')->onDelete('cascade');
            
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
