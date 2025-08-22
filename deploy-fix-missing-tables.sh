#!/bin/bash

# Deployment script to fix missing database tables
echo "🚀 Starting deployment to fix missing database tables..."

# Set error handling
set -e

# Function to run commands and handle errors
run_command() {
    echo "📋 Running: $1"
    if ! eval "$1"; then
        echo "❌ Error running: $1"
        exit 1
    fi
    echo "✅ Success: $1"
}

# Clear all caches first
echo "🧹 Clearing caches..."
run_command "php artisan config:clear"
run_command "php artisan cache:clear"
run_command "php artisan view:clear"
run_command "php artisan route:clear"

# Check migration status
echo "📊 Checking migration status..."
run_command "php artisan migrate:status"

# Force run the missing migrations
echo "🔧 Running missing migrations..."
run_command "php artisan migrate --force"

# Check if tables exist
echo "🔍 Checking if tables exist..."
run_command "php artisan tinker --execute=\"echo 'Service Charges table: '; echo Schema::hasTable('service_charges') ? 'EXISTS' : 'MISSING'; echo PHP_EOL; echo 'Contractor Service Charges table: '; echo Schema::hasTable('contractor_service_charges') ? 'EXISTS' : 'MISSING'; echo PHP_EOL;\""

# If tables still don't exist, create them manually
echo "🔧 Creating tables manually if needed..."
run_command "php artisan tinker --execute=\"
if (!Schema::hasTable('service_charges')) {
    echo 'Creating service_charges table...' . PHP_EOL;
    Schema::create('service_charges', function (\$table) {
        \$table->id();
        \$table->string('entity_type');
        \$table->unsignedBigInteger('entity_id');
        \$table->string('name');
        \$table->decimal('amount', 10, 2);
        \$table->enum('type', ['fixed', 'percentage']);
        \$table->text('description')->nullable();
        \$table->boolean('is_active')->default(true);
        \$table->unsignedBigInteger('business_id');
        \$table->unsignedBigInteger('created_by');
        \$table->timestamps();
        \$table->index(['entity_type', 'entity_id']);
        \$table->index('business_id');
        \$table->index('created_by');
        \$table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        \$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    });
    echo 'service_charges table created successfully!' . PHP_EOL;
}

if (!Schema::hasTable('contractor_service_charges')) {
    echo 'Creating contractor_service_charges table...' . PHP_EOL;
    Schema::create('contractor_service_charges', function (\$table) {
        \$table->id();
        \$table->uuid('uuid')->unique()->index();
        \$table->unsignedBigInteger('contractor_profile_id');
        \$table->decimal('amount', 10, 2);
        \$table->decimal('upper_bound', 10, 2)->nullable();
        \$table->decimal('lower_bound', 10, 2)->nullable();
        \$table->enum('type', ['fixed', 'percentage']);
        \$table->text('description')->nullable();
        \$table->boolean('is_active')->default(true);
        \$table->unsignedBigInteger('business_id');
        \$table->unsignedBigInteger('created_by');
        \$table->timestamps();
        \$table->softDeletes();
        \$table->index('contractor_profile_id');
        \$table->index('business_id');
        \$table->index('created_by');
        \$table->foreign('contractor_profile_id')->references('id')->on('contractor_profiles')->onDelete('cascade');
        \$table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        \$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    });
    echo 'contractor_service_charges table created successfully!' . PHP_EOL;
}
\""

# Run the modification migration for service_charges
echo "🔧 Running service_charges table modifications..."
run_command "php artisan tinker --execute=\"
if (Schema::hasTable('service_charges')) {
    echo 'Modifying service_charges table...' . PHP_EOL;
    
    // Add upper_bound and lower_bound columns if they don't exist
    if (!Schema::hasColumn('service_charges', 'upper_bound')) {
        Schema::table('service_charges', function (\$table) {
            \$table->decimal('upper_bound', 10, 2)->nullable()->after('amount');
        });
        echo 'Added upper_bound column' . PHP_EOL;
    }
    
    if (!Schema::hasColumn('service_charges', 'lower_bound')) {
        Schema::table('service_charges', function (\$table) {
            \$table->decimal('lower_bound', 10, 2)->nullable()->after('amount');
        });
        echo 'Added lower_bound column' . PHP_EOL;
    }
    
    // Remove name column if it exists
    if (Schema::hasColumn('service_charges', 'name')) {
        Schema::table('service_charges', function (\$table) {
            \$table->dropColumn('name');
        });
        echo 'Removed name column' . PHP_EOL;
    }
    
    echo 'service_charges table modifications completed!' . PHP_EOL;
}
\""

# Final verification
echo "✅ Final verification..."
run_command "php artisan tinker --execute=\"echo 'Final check:' . PHP_EOL; echo 'Service Charges table: '; echo Schema::hasTable('service_charges') ? 'EXISTS' : 'MISSING'; echo PHP_EOL; echo 'Contractor Service Charges table: '; echo Schema::hasTable('contractor_service_charges') ? 'EXISTS' : 'MISSING'; echo PHP_EOL;\""

# Clear caches again
echo "🧹 Final cache clearing..."
run_command "php artisan config:clear"
run_command "php artisan cache:clear"
run_command "php artisan view:clear"

echo "🎉 Deployment completed successfully!"
echo "📊 Tables should now be available on the server."
