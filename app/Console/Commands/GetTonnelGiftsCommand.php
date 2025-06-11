<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GetTonnelGiftsCommand extends Command
{
    protected $signature = 'tonnel:get-gifts 
        {--test : Run in test mode} 
        {--pages= : Number of pages to process}';

    protected $description = 'Fetch gifts from Tonnel API using Python script';

    public function handle()
    {
        $this->info('Starting Tonnel gifts fetching process...');

        $scriptPath = base_path('scripts/tonnel/activate_venv.sh');
        
        // Делаем скрипт исполняемым
        if (!is_executable($scriptPath)) {
            chmod($scriptPath, 0755);
        }

        $command = [$scriptPath, 'get_gifts.py'];

        if ($this->option('test')) {
            $command[] = '--test';
            $this->info('Running in test mode');
        }

        if ($pages = $this->option('pages')) {
            $command[] = '--pages=' . $pages;
            $this->info('Processing ' . $pages . ' pages');
        }

        $process = new Process($command);

        // Устанавливаем переменные окружения
        $process->setEnv([
            'TONNEL_LOG_LEVEL' => 'INFO',
            'TONNEL_LOG_FILE' => storage_path('logs/tonnel.log'),
            'DB_HOST' => config('database.connections.pgsql.host'),
            'DB_PORT' => config('database.connections.pgsql.port'),
            'DB_DATABASE' => config('database.connections.pgsql.database'),
            'DB_USERNAME' => config('database.connections.pgsql.username'),
            'DB_PASSWORD' => config('database.connections.pgsql.password'),
        ]);

        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('Failed to execute Python script');
            return 1;
        }

        $this->info('Tonnel gifts fetching completed successfully');
        return 0;
    }
} 