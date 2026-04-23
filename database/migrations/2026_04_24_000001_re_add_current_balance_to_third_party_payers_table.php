<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('third_party_payers', 'current_balance')) {
            Schema::table('third_party_payers', function (Blueprint $table) {
                $table->decimal('current_balance', 15, 2)->default(0.00)->after('credit_limit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('third_party_payers', 'current_balance')) {
            Schema::table('third_party_payers', function (Blueprint $table) {
                $table->dropColumn('current_balance');
            });
        }
    }
};
