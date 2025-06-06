<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <title>Избранные подарки - Giftostat</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html, body {
                height: 100%;
                overflow: hidden;
                position: fixed;
                width: 100%;
                -webkit-overflow-scrolling: touch;
            }
            main {
                height: 100%;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: env(safe-area-inset-bottom);
            }
            .sort-link {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                color: inherit;
                text-decoration: none;
            }
            .sort-link:hover {
                color: #4B5563;
            }
            .sort-link.active {
                color: #111827;
                font-weight: 500;
            }
        </style>
    </head>
    <body class="bg-white text-gray-900">
        <main class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-medium">Избранные подарки</h1>
                    <a href="{{ route('gifts.index') }}" class="text-blue-600 hover:text-blue-800">
                        Все подарки
                    </a>
                </div>
                @if($favoriteGifts->isNotEmpty())
                    <div class="text-lg font-medium">
                        Общая стоимость: <span class="text-green-600">{{ number_format($totalPrice, 2) }} TON</span>
                    </div>
                @endif
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображение</th>
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sortField === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="sort-link {{ $sortField === 'name' ? 'active' : '' }}">
                                    Название
                                    @if($sortField === 'name')
                                        @if($sortDirection === 'asc')
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Модель</th>
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Последняя floor цена</th>
                            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата обновления</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($favoriteGifts as $gift)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($favoriteGifts->currentPage() - 1) * $favoriteGifts->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="{{ $gift->image ? asset('images/gifts/' . $gift->image) : asset('images/gifts/default.svg') }}" 
                                        alt="{{ $gift->name }}" 
                                        class="w-16">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $gift->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $gift->model }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($gift->prices->isNotEmpty())
                                        {{ $gift->prices->last()->price }} TON
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($gift->prices->isNotEmpty())
                                        {{ \Carbon\Carbon::parse($gift->prices->last()->checked_at)->setTimezone('Europe/Moscow')->format('d.m.Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Избранных подарков пока нет</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $favoriteGifts->onEachSide(1)->links() }}
            </div>
        </main>
    </body>
</html> 