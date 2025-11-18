<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Check payment statuses every minute
        $schedule->command('payments:check-status')->everyMinute();
        
        // Process extended service queue items every hour
        $schedule->command('service-queue:process-extended-items')->hourly();
        
        // Expire visit IDs at midnight, extend for clients with pending/in-progress items
        $schedule->command('visits:expire')->dailyAt('00:00');
        
        // Check and auto-reject overdue withdrawal requests every hour
        $schedule->command('withdrawals:auto-reject-overdue')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
