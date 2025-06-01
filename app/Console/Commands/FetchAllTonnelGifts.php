<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\GiftPrice;
use App\Services\TonnelService;
use Illuminate\Console\Command;

class FetchAllTonnelGifts extends Command
{
    protected $signature = 'tonnel:fetch-all-gifts {--limit=30 : Количество подарков на страницу}';
    protected $description = 'Выгрузить все подарки с Tonnel Marketplace и сохранить статистику цен';

    public function handle(TonnelService $service)
    {
        $limit = (int) $this->option('limit');
        $this->info("Начинаю массовую выгрузку всех подарков с лимитом $limit...");

        $allGifts = $service->fetchAllGifts($limit);
        $this->info('Всего подарков получено: ' . count($allGifts));

        $now = now();
        $processed = 0;
        $errors = 0;

        foreach ($allGifts as $giftData) {
            try {
                // Создаем или находим подарок
                $gift = Gift::firstOrCreate(
                    [
                        'name' => $giftData['name'],
                        'model' => $giftData['model']
                    ]
                );

                // Сохраняем цену
                GiftPrice::create([
                    'gift_id' => $gift->id,
                    'price' => $giftData['price'],
                    'checked_at' => $now
                ]);

                $processed++;
            } catch (\Exception $e) {
                $this->error("Ошибка при обработке подарка {$giftData['name']} ({$giftData['model']}): " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Обработано подарков: $processed");
        if ($errors > 0) {
            $this->warn("Ошибок при обработке: $errors");
        }

        return 0;
    }
} 