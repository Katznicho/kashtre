#!/bin/bash

# Script to set up the Laravel cron job for payment status checking
# This script should be run on the server to configure the cron job

echo "🚀 Setting up Laravel cron job for payment status checking..."

# Get the current directory (should be the Laravel project root)
PROJECT_DIR=$(pwd)
CRON_COMMAND="* * * * * cd $PROJECT_DIR && php artisan schedule:run >> /dev/null 2>&1"

echo "📋 Project directory: $PROJECT_DIR"
echo "📋 Cron command: $CRON_COMMAND"

# Check if the cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "⚠️  Laravel cron job already exists in crontab"
    echo "📋 Current crontab entries:"
    crontab -l
else
    echo "➕ Adding Laravel cron job to crontab..."
    
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_COMMAND") | crontab -
    
    if [ $? -eq 0 ]; then
        echo "✅ Laravel cron job added successfully!"
        echo "📋 Updated crontab entries:"
        crontab -l
    else
        echo "❌ Failed to add cron job"
        exit 1
    fi
fi

echo ""
echo "🔍 Verification:"
echo "📋 The cron job will run every minute and execute:"
echo "   - php artisan schedule:run"
echo "   - This will trigger the payments:check-status command every minute"
echo "   - The command will check YoAPI payment statuses using external_reference field"
echo ""
echo "📋 To manually test the command:"
echo "   php artisan payments:check-status"
echo ""
echo "📋 To view Laravel logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "🎉 Cron job setup completed!"
