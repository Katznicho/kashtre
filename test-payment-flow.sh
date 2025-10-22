#!/bin/bash

# Test Payment Flow Script for Local Development
# This script simulates the complete payment flow locally

echo "🧪 Kashtre Payment Flow Test Script"
echo "===================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "📋 Available Commands:"
echo "1. Test complete flow (reset + simulate)"
echo "2. Just simulate successful payments"
echo "3. Reset transactions to pending"
echo "4. Show current status"
echo "5. Show suspense accounts"
echo ""

read -p "Choose an option (1-5): " choice

case $choice in
    1)
        echo "🔄 Running complete test flow..."
        php artisan payments:test-flow --reset --limit=10
        ;;
    2)
        echo "🎯 Simulating successful payments..."
        php artisan payments:simulate-success --limit=10
        ;;
    3)
        echo "🔄 Resetting transactions..."
        php artisan payments:reset-for-testing --confirm
        ;;
    4)
        echo "📊 Current Status:"
        php artisan payments:test-flow --limit=0
        ;;
    5)
        echo "💰 Suspense Accounts:"
        php artisan tinker --execute="
            \$accounts = App\Models\MoneyAccount::whereIn('type', ['package_suspense_account', 'general_suspense_account', 'kashtre_suspense_account'])->get();
            echo 'Suspense Account Balances:' . PHP_EOL;
            foreach(\$accounts as \$account) {
                echo \$account->type . ': ' . number_format(\$account->balance, 0) . ' UGX' . PHP_EOL;
            }
        "
        ;;
    *)
        echo "❌ Invalid option. Please choose 1-5."
        exit 1
        ;;
esac

echo ""
echo "✅ Command completed!"
echo ""
echo "💡 Tips:"
echo "• Check logs: tail -f storage/logs/laravel.log"
echo "• View suspense accounts: Visit /suspense-accounts in your browser"
echo "• Run again: ./test-payment-flow.sh"
