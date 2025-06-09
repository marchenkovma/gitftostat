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
        // Проверка цен подарков каждые 2 часа
        $schedule->command('tonnel:check-gift-prices')
         ->everyMinute()
         ->before(function () {
             if (\Illuminate\Support\Facades\Cache::has('tonnel:running')) {
                 return false; // Пропускаем выполнение, если задача уже запущена
             }
             Cache::put('tonnel:running', true, 5); // Устанавливаем флаг на 5 минут
         })
         ->after(function () {
             Cache::forget('tonnel:running'); // Очищаем флаг после выполнения
         })
         ->appendOutputTo(storage_path('logs/tonnel-scheduler.log'));

        
        $schedule->command('inspire')->everyMinute()
            ->appendOutputTo(storage_path('logs/test-scheduler.log'));;
    })
    ->create();
