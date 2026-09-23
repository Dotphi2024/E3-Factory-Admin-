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
        // Daily Birthday Wishes at 09:00 AM
        $schedule->command('participants:send-birthday-wishes')->dailyAt('09:00');

        // Check and send due scheduled Value Posts every 15 minutes
        $schedule->command('value-posts:send-scheduled')->everyFifteenMinutes();

        // Send upcoming session WhatsApp reminders every morning at 08:00 AM
        $schedule->command('session:send-whatsapp-reminders')->dailyAt('08:00');

        // Send pending fee WhatsApp reminders every Monday at 10:00 AM
        $schedule->command('participants:send-fee-due-reminders')->weeklyOn(1, '10:00');
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
