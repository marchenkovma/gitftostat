<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\GiftPrice;
use App\Services\TonnelService;
use Illuminate\Console\Command;

class FetchTestTonnelGifts extends Command
{
    protected $signature = 'tonnel:fetch-test {--limit=30 : Количество подарков на страницу}';
    protected $description = 'Тестовая выгрузка 2 страниц подарков с Tonnel Marketplace';

    public function handle(TonnelService $service)
    {
        $limit = (int) $this->option('limit');
        $this->info("Начинаю тестовую выгрузку 2 страниц подарков с лимитом $limit...");

        $now = now();
        $processed = 0;
        $errors = 0;

        // Получаем первую страницу
        $this->info("Получаем первую страницу...");
        $page1Gifts = $service->fetchGifts(1, $limit);
        $this->info("Получено подарков на первой странице: " . count($page1Gifts));

        // Получаем вторую страницу
        $this->info("Получаем вторую страницу...");
        $page2Gifts = $service->fetchGifts(2, $limit);
        $this->info("Получено подарков на второй странице: " . count($page2Gifts));

        // Объединяем подарки
        $allGifts = array_merge($page1Gifts, $page2Gifts);
        $this->info("Всего подарков получено: " . count($allGifts));

        // Показываем первые 5 подарков для примера
        $this->table(
            ['Name', 'Model', 'Price'],
            collect($allGifts)->take(5)->map(fn($gift) => [
                $gift['name'] ?? 'N/A',
                $gift['model'] ?? 'N/A',
                $gift['price'] ?? 'N/A'
            ])
        );

        // Сохраняем в базу
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