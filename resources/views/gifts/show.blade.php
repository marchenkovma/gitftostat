<x-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('gifts.index') }}" class="text-indigo-600 hover:text-indigo-900">
                            ← Назад к списку
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <h1 class="text-2xl font-semibold mb-4">{{ $gift->name }}</h1>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h2 class="text-lg font-medium mb-2">Основная информация</h2>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Модель</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $gift->model }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Текущая цена</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $gift->price }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Статус</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $gift->status }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h2 class="text-lg font-medium mb-2">История цен</h2>
                                    @if($gift->prices->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach($gift->prices as $price)
                                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                    <span class="text-sm text-gray-900">{{ $price->price }}</span>
                                                    <span class="text-sm text-gray-500">{{ $price->checked_at->format('d.m.Y H:i') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">История цен отсутствует</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout> 