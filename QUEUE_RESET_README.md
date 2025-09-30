# Service Point Queue Reset System

This system allows you to reset service delivery queues for testing purposes. It provides both web interface and command-line options to clear queued clients at service points.

## Features

- **Individual Service Point Reset**: Reset queues for a specific service point
- **Global Reset**: Reset all service point queues at once
- **Web Interface**: Easy-to-use buttons in the service points management page
- **Command Line**: Artisan commands for automation and testing
- **Safety Confirmation**: Confirmation dialogs to prevent accidental resets
- **Audit Logging**: All reset operations are logged for tracking

## How It Works

When you reset a queue, all items with status `pending`, `in_progress`, or `partially_done` are marked as `cancelled`. This effectively clears the queue while maintaining a record of what was there.

## Web Interface Usage

### 1. Navigate to Service Points
Go to the Service Points Management page in your application.

### 2. Reset Individual Service Point
- Find the service point you want to reset
- If there are pending items, you'll see a "Reset Queue" button
- Click the button and confirm the action
- The queue will be cleared and the page will refresh

### 3. Reset All Service Points
- Use the "Reset All Queues" button at the top of the page
- Confirm the action when prompted
- All service point queues will be cleared

## Command Line Usage

### Reset Specific Service Point
```bash
php artisan service-queues:reset --service-point=1
```

### Reset All Service Points
```bash
php artisan service-queues:reset --all
```

### Force Reset (No Confirmation)
```bash
php artisan service-queues:reset --all --force
```

### Help
```bash
php artisan service-queues:reset --help
```

## API Endpoints

### Reset Specific Service Point
```
POST /service-delivery-queues/service-point/{servicePointId}/reset
```

### Reset All Service Points
```
POST /service-delivery-queues/reset-all
```

## Security

- Users can only reset queues for service points they have access to
- Global reset requires special permissions (`Reset All Service Queues`)
- All operations are logged for audit purposes
- CSRF protection is enabled for web requests

## Testing Scenarios

This system is perfect for:
- **Development Testing**: Clear queues between test runs
- **Demo Purposes**: Reset queues before presentations
- **Training**: Start fresh for new users
- **Debugging**: Clear queues to isolate issues

## Monitoring

After resetting queues:
- Check the logs for reset operations
- Verify that pending items are now marked as cancelled
- Confirm that new items can be queued normally

## Troubleshooting

### Permission Denied
- Ensure you have access to the service point
- Check if you have the required permissions for global reset

### No Items Reset
- Verify there are actually pending items in the queue
- Check the item statuses (pending, in_progress, partially_done)

### JavaScript Errors
- Ensure SweetAlert is loaded
- Check browser console for any JavaScript errors
- Verify CSRF token is available

## File Locations

- **Controller**: `app/Http/Controllers/ServiceDeliveryQueueController.php`
- **Command**: `app/Console/Commands/ResetServiceQueues.php`
- **Routes**: `routes/web.php`
- **View**: `resources/views/livewire/service_points/list-service-points.blade.php`
- **Model**: `app/Models/ServiceDeliveryQueue.php`

## Example Usage

```bash
# Reset queues for service point ID 5
php artisan service-queues:reset --service-point=5

# Reset all queues without confirmation
php artisan service-queues:reset --all --force

# Check what will be reset first
php artisan service-queues:reset --all
```

## Notes

- Resetting queues is irreversible - cancelled items cannot be restored
- This is intended for testing and development purposes
- In production, consider implementing additional safeguards
- All reset operations are logged for compliance and debugging






















