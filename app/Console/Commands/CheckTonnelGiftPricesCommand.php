<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CheckTonnelGiftPricesCommand extends Command
{
    protected $signature = 'tonnel:check-gift-prices {--test : Run in test mode}';
    protected $description = 'Check prices for all gifts in the database';

    public function handle()
    {
        $this->info('Starting gift prices check...');

        // Устанавливаем переменные окружения
        $env = [
            'TONNEL_LOG_LEVEL' => 'INFO',
            'TONNEL_LOG_FILE' => storage_path('logs/tonnel.log'),
            'DB_HOST' => config('database.connections.pgsql.host'),
            'DB_PORT' => config('database.connections.pgsql.port'),
            'DB_DATABASE' => config('database.connections.pgsql.database'),
            'DB_USERNAME' => config('database.connections.pgsql.username'),
            'DB_PASSWORD' => config('database.connections.pgsql.password'),
        ];

        // Делаем скрипт активации исполняемым
        $activateScript = base_path('scripts/tonnel/activate_venv.sh');
        chmod($activateScript, 0755);

        // Запускаем Python скрипт
        $process = new Process([
            $activateScript,
            'check_gift_price.py',
            $this->option('test') ? '--test' : null
        ]);
        $process->setEnv($env);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('Failed to check gift prices');
            return 1;
        }

        $this->info('Gift prices check completed successfully');
        return 0;
    }
} 