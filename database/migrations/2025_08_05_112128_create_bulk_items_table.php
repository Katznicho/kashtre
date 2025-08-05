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
        Schema::create('bulk_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('bulk_item_id')->constrained('items')->onDelete('cascade'); // The main bulk item
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade'); // The item included in the bulk
            $table->integer('fixed_quantity')->default(1); // Fixed quantity for this item
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_items');
    }
};
