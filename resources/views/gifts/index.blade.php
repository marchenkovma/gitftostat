<x-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-semibold mb-4">Подарки</h1>

                    <!-- Фильтры -->
                    <div class="mb-6">
                        <form action="{{ route('gifts.index') }}" method="GET" class="flex gap-4">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Цена</label>
                                <select name="price" id="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Все цены</option>
                                    <option value="asc" {{ request('price') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                    <option value="desc" {{ request('price') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                                </select>
                            </div>

                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Поиск</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Поиск по названию...">
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Применить
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Список подарков -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($gifts as $gift)
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold mb-2">{{ $gift->name }}</h2>
                                    <p class="text-gray-600 mb-2">Модель: {{ $gift->model }}</p>
                                    <p class="text-gray-600 mb-2">Цена: {{ $gift->price }}</p>
                                    <a href="{{ route('gifts.show', $gift) }}" 
                                        class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500">Подарки не найдены</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Пагинация -->
                    <div class="mt-6">
                        {{ $gifts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout> 