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
        Schema::create('withdrawal_setting_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id');
            $table->string('approver_type'); // 'user' or 'contractor'
            $table->enum('approver_level', ['business', 'kashtre']); // business or kashtre level
            $table->timestamps();
            
            // Ensure unique approver per setting and level
            $table->unique(['withdrawal_setting_id', 'approver_id', 'approver_type', 'approver_level'], 'unique_approver_per_setting_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_setting_approvers');
    }
};
