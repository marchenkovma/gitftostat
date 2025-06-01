<?php

namespace App\Services;

use App\Models\Gift;
use App\Models\GiftPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TonnelService
{
    protected $client;
    protected $lastResponse;
    protected string $baseUrl;

    public function __construct()
    {
        $this->client = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Origin' => 'https://gifts2.tonnel.network',
            'Referer' => 'https://gifts2.tonnel.network/'
        ]);
        $this->baseUrl = 'https://gifts2.tonnel.network/api';
    }

    public function fetchGifts(int $page = 1, int $limit = 10, array $filter = []): array
    {
        try {
            $requestData = [
                'page' => $page,
                'limit' => $limit,
                'sort' => '{"_id":1}',
                'filter' => '{"buyer":{"$exists":false},"refunded":{"$ne":true},"price":{"$exists":true}}',
                'price_range' => null,
                'user_auth' => ''
            ];

            Log::info('Отправляем запрос к Tonnel API', [
                'url' => $this->baseUrl . '/pageGifts',
                'data' => $requestData
            ]);

            $response = $this->client->post($this->baseUrl . '/pageGifts', $requestData);

            $this->lastResponse = $response->json();

            Log::info('Получен ответ от Tonnel API', [
                'status' => $response->status(),
                'response' => $this->lastResponse
            ]);

            if ($response->failed()) {
                Log::error('Failed to fetch gifts from Tonnel', [
                    'status' => $response->status(),
                    'response' => $this->lastResponse
                ]);
                return [];
            }

            if (!is_array($this->lastResponse)) {
                Log::error('Invalid response format from Tonnel API', [
                    'response' => $this->lastResponse
                ]);
                return [];
            }

            $processedGifts = [];

            foreach ($this->lastResponse as $gift) {
                try {
                    $processedGifts[] = [
                        'name' => $gift['name'] ?? null,
                        'model' => $gift['model'] ?? null,
                        'price' => $gift['price'] ?? null,
                    ];
                } catch (\Exception $e) {
                    Log::error("Ошибка при обработке подарка", [
                        'gift' => $gift,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            return $processedGifts;
        } catch (\Exception $e) {
            Log::error('Error fetching gifts from Tonnel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getLastResponse(): array
    {
        return (array) $this->lastResponse;
    }

    public function fetchAllGifts(int $limit = 30): array
    {
        $allGifts = [];
        $page = 1;
        $emptyResponses = 0;
        $maxEmptyResponses = 3; // Максимальное количество пустых ответов подряд

        while (true) {
            $gifts = $this->fetchGifts($page, $limit);
            
            if (empty($gifts)) {
                $emptyResponses++;
                if ($emptyResponses >= $maxEmptyResponses) {
                    Log::info('Прекращаем получение данных после ' . $maxEmptyResponses . ' пустых ответов подряд');
                    break;
                }
            } else {
                $emptyResponses = 0;
                $allGifts = array_merge($allGifts, $gifts);
            }

            $page++;
        }

        Log::info('Завершено получение подарков', [
            'total_pages' => $page - 1,
            'total_gifts' => count($allGifts)
        ]);

        return $allGifts;
    }
} 