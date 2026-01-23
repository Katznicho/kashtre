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
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('email')->nullable()->after('code');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->text('head_office_address')->nullable()->after('address');
            $table->text('postal_address')->nullable()->after('head_office_address');
            $table->string('website')->nullable()->after('postal_address');
            $table->string('third_party_business_id')->nullable()->after('website');
            $table->string('third_party_user_id')->nullable()->after('third_party_business_id');
            $table->text('third_party_username')->nullable()->after('third_party_user_id');
            $table->text('third_party_password')->nullable()->after('third_party_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'email',
                'phone',
                'address',
                'head_office_address',
                'postal_address',
                'website',
                'third_party_business_id',
                'third_party_user_id',
                'third_party_username',
                'third_party_password',
            ]);
        });
    }
};
