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
        // ... existing code ...
        
        // Проверка цен подарков каждые 2 часа
        $schedule->command('tonnel:check-gift-prices')
                ->everyTwoHours()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/tonnel-scheduler.log'));
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