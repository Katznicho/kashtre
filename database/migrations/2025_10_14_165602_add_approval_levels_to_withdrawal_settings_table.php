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
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            // Add fields for 3-level approval system
            // Business level approvers (1-2 people)
            $table->integer('min_business_initiators')->default(1)->after('min_kashtre_approvers');
            $table->integer('max_business_initiators')->default(2)->after('min_business_initiators');
            $table->integer('min_business_authorizers')->default(1)->after('max_business_initiators');
            $table->integer('max_business_authorizers')->default(2)->after('min_business_authorizers');
            $table->integer('min_business_approvers')->default(1)->after('max_business_authorizers');
            $table->integer('max_business_approvers')->default(2)->after('min_business_approvers');
            
            // Kashtre level approvers (1-2 people)
            $table->integer('min_kashtre_initiators')->default(1)->after('max_business_approvers');
            $table->integer('max_kashtre_initiators')->default(2)->after('min_kashtre_initiators');
            $table->integer('min_kashtre_authorizers')->default(1)->after('max_kashtre_initiators');
            $table->integer('max_kashtre_authorizers')->default(2)->after('min_kashtre_authorizers');
            $table->integer('min_kashtre_approvers')->default(1)->after('max_kashtre_authorizers');
            $table->integer('max_kashtre_approvers')->default(2)->after('min_kashtre_approvers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            // Drop the approval level fields
            $table->dropColumn([
                'min_business_initiators',
                'max_business_initiators',
                'min_business_authorizers',
                'max_business_authorizers',
                'min_business_approvers',
                'max_business_approvers',
                'min_kashtre_initiators',
                'max_kashtre_initiators',
                'min_kashtre_authorizers',
                'max_kashtre_authorizers',
                'min_kashtre_approvers',
                'max_kashtre_approvers'
            ]);
        });
    }
};
