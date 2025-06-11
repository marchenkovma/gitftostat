<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('tonnel:check-gift-prices')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/tonnel-scheduler.log'));

        // Запуск команды загрузки изображений в 5 утра
        $schedule->command('gifts:download-images')
                ->dailyAt('05:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/tonnel-scheduler.log'));
    })
    ->create();
