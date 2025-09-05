# Cron Job Setup for Payment Status Checking

This document explains how to set up the cron job for automatic YoAPI payment status checking.

## Overview

The system uses a Laravel scheduled command (`payments:check-status`) that runs every minute to check the status of pending YoAPI payments and update them accordingly.

## Current Configuration

### Laravel Scheduler
- **File**: `app/Console/Kernel.php`
- **Command**: `payments:check-status`
- **Schedule**: Every minute (`->everyMinute()`)
- **Purpose**: Check and update YoAPI payment statuses using `external_reference` field

### Command Details
- **File**: `app/Console/Commands/CheckPaymentStatus.php`
- **Signature**: `payments:check-status`
- **Description**: Check and update YoAPI payment statuses using external_reference field
- **Logging**: Comprehensive logging to `storage/logs/laravel.log`

## Server Setup

### Option 1: Using the Setup Script (Recommended)

1. **Upload the setup script to your server**:
   ```bash
   # Make sure you're in the Laravel project root directory
   chmod +x setup-cron-job.sh
   ./setup-cron-job.sh
   ```

### Option 2: Manual Setup

1. **Access your server via SSH**

2. **Navigate to your Laravel project directory**:
   ```bash
   cd /path/to/your/laravel/project
   ```

3. **Add the cron job to crontab**:
   ```bash
   crontab -e
   ```

4. **Add this line to the crontab**:
   ```bash
   * * * * * cd /path/to/your/laravel/project && php artisan schedule:run >> /dev/null 2>&1
   ```

5. **Save and exit the editor**

## Verification

### Check if the cron job is running:
```bash
# View current crontab
crontab -l

# Test the command manually
php artisan payments:check-status

# Check Laravel logs
tail -f storage/logs/laravel.log
```

### Expected Log Output:
```
[2025-09-05 17:30:00] local.INFO: Starting payment status check for YoAPI transactions
[2025-09-05 17:30:00] local.INFO: Found 2 pending YoAPI transactions
[2025-09-05 17:30:01] local.INFO: YoAPI status check response for transaction 123
[2025-09-05 17:30:01] local.INFO: Payment status check completed successfully
```

## How It Works

1. **Every minute**, the Laravel scheduler runs `php artisan schedule:run`
2. **The scheduler** executes the `payments:check-status` command
3. **The command** queries for pending transactions with:
   - `status = 'pending'`
   - `method = 'mobile_money'`
   - `provider = 'yo'`
   - `external_reference` is not null
4. **For each transaction**, it calls YoAPI's `ac_transaction_check_status` using the `external_reference`
5. **Based on the response**, it updates the transaction status:
   - `SUCCEEDED` → `status = 'completed'`
   - `FAILED` → `status = 'failed'`
6. **All actions are logged** for debugging and monitoring

## Troubleshooting

### Common Issues:

1. **Cron job not running**:
   - Check if crontab is properly configured: `crontab -l`
   - Verify the path in the cron command is correct
   - Check server cron service is running: `systemctl status cron`

2. **Command not found**:
   - Ensure PHP is in the system PATH
   - Use full path to PHP: `/usr/bin/php artisan schedule:run`

3. **Permission issues**:
   - Ensure the web server user has access to the Laravel project
   - Check file permissions: `chmod -R 755 storage bootstrap/cache`

4. **No transactions being processed**:
   - Check if there are pending YoAPI transactions in the database
   - Verify the `external_reference` field is populated
   - Check Laravel logs for error messages

### Manual Testing:
```bash
# Test the command directly
php artisan payments:check-status

# Check for pending transactions
php artisan tinker
>>> App\Models\Transaction::where('status', 'pending')->where('provider', 'yo')->count()
```

## Monitoring

### Log Files:
- **Laravel Logs**: `storage/logs/laravel.log`
- **System Cron Logs**: `/var/log/cron` (varies by system)

### Key Metrics to Monitor:
- Number of pending transactions processed per minute
- Success/failure rates of YoAPI status checks
- Response times from YoAPI
- Any error messages in the logs

## Security Notes

- The cron job runs with the same permissions as the web server user
- Ensure proper file permissions are set on the Laravel project
- Monitor logs for any suspicious activity
- Keep Laravel and dependencies updated for security patches
