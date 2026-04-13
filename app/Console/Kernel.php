<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule cache cleanup every hour
        $schedule->command('cache:clear-expired')->hourly();
        
        // Schedule daily backup at 2 AM
        $schedule->command('backup:database')->dailyAt('02:00');
        
        // Schedule low stock check twice daily
        $schedule->command('inventory:check-low-stock')->twiceDaily(9, 15); // 9 AM and 3 PM
    }
}