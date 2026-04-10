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
        // Existing scheduled notifications
        $schedule->command('notifications:send-scheduled')->everyMinute()->withoutOverlapping()->runInBackground();

        // Automated random notifications from library (9 AM, 1 PM, 8 PM)
        $schedule->command('notifications:send-random')->dailyAt('09:00')->withoutOverlapping()->runInBackground();
        $schedule->command('notifications:send-random')->dailyAt('13:00')->withoutOverlapping()->runInBackground();
        $schedule->command('notifications:send-random')->dailyAt('20:00')->withoutOverlapping()->runInBackground();
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