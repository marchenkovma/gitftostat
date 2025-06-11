<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckTonnelGiftPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tonnel:check-gift-prices {--test} {--pages=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check gift prices from Tonnel API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Tonnel gift prices checking process...');

        // Путь к скрипту активации виртуального окружения
        $activateScript = base_path('scripts/tonnel/activate_venv.sh');

        // Делаем скрипт исполняемым, если он еще не исполняемый
        if (!is_executable($activateScript)) {
            chmod($activateScript, 0755);
        }

        // Формируем команду для запуска Python скрипта
        $command = [$activateScript, 'check_gift_price.py'];

        // Добавляем опции, если они указаны
        if ($this->option('test')) {
            $command[] = '--test';
        }
        if ($this->option('pages')) {
            $command[] = '--pages=' . $this->option('pages');
        }

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

        // Запускаем процесс
        $process = proc_open(
            implode(' ', $command),
            [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w']   // stderr
            ],
            $pipes,
            null,
            $env
        );

        if (is_resource($process)) {
            // Читаем вывод процесса
            while (!feof($pipes[1])) {
                $output = fgets($pipes[1]);
                if ($output) {
                    $this->line(trim($output));
                }
            }

            // Закрываем все потоки
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            // Получаем код возврата
            $returnCode = proc_close($process);

            if ($returnCode !== 0) {
                $this->error('Failed to check gift prices');
                Log::error('Failed to check gift prices', ['return_code' => $returnCode]);
            } else {
                $this->info('Gift prices checking completed successfully');
            }
        } else {
            $this->error('Failed to start the process');
            Log::error('Failed to start the process');
        }
    }
} 