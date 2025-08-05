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
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('package_item_id')->constrained('items')->onDelete('cascade'); // The main package item
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade'); // The item included in the package
            $table->integer('max_quantity')->default(1); // Maximum allowed quantity for this item
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
        Schema::dropIfExists('package_items');
    }
};
