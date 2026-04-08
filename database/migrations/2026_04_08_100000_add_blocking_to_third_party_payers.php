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
        Schema::table('third_party_payers', function (Blueprint $table) {
            $table->string('block_reason')->nullable()->after('status')->comment('Reason for blocking/suspension');
            $table->dateTime('blocked_at')->nullable()->after('block_reason')->comment('When the vendor was blocked/suspended');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at')->comment('User who blocked the vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('third_party_payers', function (Blueprint $table) {
            $table->dropColumn(['block_reason', 'blocked_at', 'blocked_by']);
        });
    }
};
