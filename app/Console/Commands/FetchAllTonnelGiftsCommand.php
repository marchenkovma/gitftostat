<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class FetchAllTonnelGiftsCommand extends Command
{
    protected $signature = 'tonnel:fetch-all-gifts {--test : Run in test mode} {--pages=2 : Number of pages to process in test mode}';
    protected $description = 'Fetch all gifts from Tonnel API using Python script';

    public function handle()
    {
        $this->info('Starting Tonnel gifts fetching process...');

        $scriptPath = base_path('scripts/tonnel/activate_venv.sh');
        
        // Делаем скрипт исполняемым
        if (!is_executable($scriptPath)) {
            chmod($scriptPath, 0755);
        }

        $command = [$scriptPath];

        if ($this->option('test')) {
            $command[] = '--test';
            $command[] = '--pages=' . $this->option('pages');
            $this->info('Running in test mode with ' . $this->option('pages') . ' pages');
        }

        $process = new Process($command);

        // Устанавливаем только уровень логирования
        $process->setEnv([
            'TONNEL_LOG_LEVEL' => 'INFO'
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