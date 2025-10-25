<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the foreign key constraint with SET NULL (it may not exist)
        try {
            DB::statement('ALTER TABLE contractor_profiles ADD CONSTRAINT contractor_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        } catch (\Exception $e) {
            // If constraint already exists, drop and recreate it
            DB::statement('ALTER TABLE contractor_profiles DROP FOREIGN KEY contractor_profiles_user_id_foreign');
            DB::statement('ALTER TABLE contractor_profiles ADD CONSTRAINT contractor_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to revert the foreign key constraint
        DB::statement('ALTER TABLE contractor_profiles DROP FOREIGN KEY contractor_profiles_user_id_foreign');
        DB::statement('ALTER TABLE contractor_profiles ADD CONSTRAINT contractor_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    }
};
