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
        // Check for birthdays and anniversaries daily at 8 AM
        $schedule->command('app:send-date-notifications')->dailyAt('08:00');
        
        // Also send notifications 7 days in advance
        $schedule->command('app:send-date-notifications --days=7')->dailyAt('08:30');
        
        // Process recurring donations daily at 1 AM
        $schedule->command('donations:process-recurring')->dailyAt('01:00');
        
        // Generate annual tax receipts on January 5th of each year for the previous year
        $schedule->command('tax-receipts:generate-annual')->yearlyOn(1, 5, '02:00');

        // Process and send due event reminders every five minutes
        $schedule->command('events:process-reminders')->everyFiveMinutes();
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
