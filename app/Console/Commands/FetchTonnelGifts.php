<?php

namespace App\Console\Commands;

use App\Services\TonnelService;
use Illuminate\Console\Command;

class FetchTonnelGifts extends Command
{
    protected $signature = 'tonnel:fetch-gifts 
                            {--page=1 : Номер страницы}
                            {--limit=10 : Количество подарков на странице}
                            {--name= : Фильтр по имени подарка}
                            {--model= : Фильтр по модели подарка}';

    protected $description = 'Получить подарки с Tonnel Marketplace';

    public function handle(TonnelService $service)
    {
        $this->info('Получаем подарки с Tonnel Marketplace...');

        $filter = [];
        
        if ($name = $this->option('name')) {
            $filter['name'] = $name;
        }
        
        if ($model = $this->option('model')) {
            $filter['model'] = $model;
        }

        $gifts = $service->fetchGifts(
            $this->option('page'),
            $this->option('limit'),
            $filter
        );

        if (empty($gifts)) {
            $this->error('Не удалось получить подарки');
            return 1;
        }

        $this->info('Получено подарков: ' . count($gifts));
        $this->table(
            ['ID', 'Name', 'Model', 'Price'],
            collect($gifts)->map(fn($gift) => [
                $gift['_id'] ?? 'N/A',
                $gift['name'] ?? 'N/A',
                $gift['model'] ?? 'N/A',
                $gift['price'] ?? 'N/A'
            ])
        );

        return 0;
    }
} 