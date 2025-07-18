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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['service', 'good', 'package', 'bulk']);
            $table->text('description')->nullable();
            $table->foreignId('group_id')->nullable();
            $table->foreignId('subgroup_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('uom_id')->nullable();
            $table->foreignId('service_point_id')->nullable();
            $table->string('default_price')->default(0);
            $table->unsignedTinyInteger('hospital_share')->default(100); // %
            $table->foreignId('contractor_account_id')->nullable();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // Allows for soft deletion of items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
