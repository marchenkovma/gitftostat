<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DownloadGiftImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gifts:download-images {--test : Run in test mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download images for all gifts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting gift images download...');
        
        $scriptDir = base_path('scripts/tonnel');
        $activateScript = "{$scriptDir}/activate_venv.sh";
        
        // Делаем скрипт активации исполняемым
        if (!is_executable($activateScript)) {
            chmod($activateScript, 0755);
        }
        
        $command = "{$activateScript} download_gift_images.py";
        
        if ($this->option('test')) {
            $command .= ' --test';
        }

        $output = [];
        $returnVar = 0;
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            $this->error('Failed to execute Python script');
            Log::error('Failed to execute gift images download script', [
                'output' => $output,
                'return_var' => $returnVar
            ]);
            return 1;
        }

        $this->info('Gift images download completed successfully');
        return 0;
    }
}
