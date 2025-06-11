<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-medium text-white">{{ __('Gift list') }}</h1>
        <a href="{{ route('favorites.index') }}" class="text-blue-400 hover:text-blue-300">
            {{ __('Favorites Gift')}}
        </a>
    </div>

    <div class="mb-6 space-y-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Name') }}</label>
                <select id="name" 
                        class="w-full rounded-lg border-gray-700 bg-gray-800 text-white focus:border-blue-500 focus:ring-blue-500"
                        onchange="handleNameChange()">
                    <option value="">{{ __('Select name') }}</option>
                    @foreach($names as $name)
                        <option value="{{ $name }}" {{ request('name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex-1">
                <label for="model" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Model') }}</label>
                <select id="model" 
                        class="w-full rounded-lg border-gray-700 bg-gray-800 text-white focus:border-blue-500 focus:ring-blue-500"
                        onchange="handleModelChange()">
                    <option value="">{{ __('Select model') }}</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            @if(request('name') || request('model'))
                <a href="{{ route('gifts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white" onclick="clearFilters(event)">
                    {{ __('Clear') }}
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @foreach($gifts as $gift)
            <x-gift-card :gift="$gift" />
        @endforeach
    </div>

    <div class="mt-6">
        {{ $gifts->links() }}
    </div>

    <script>
        // Объект с моделями для каждого имени
        const modelsByName = @json($modelsByName);
        let isInitialLoad = true;

        // Функция для обновления списка моделей
        function updateModels() {
            const nameSelect = document.getElementById('name');
            const modelSelect = document.getElementById('model');
            const selectedName = nameSelect.value;

            // Очищаем текущий список моделей
            modelSelect.innerHTML = '<option value="">{{ __('Select model') }}</option>';

            // Если имя выбрано, добавляем соответствующие модели
            if (selectedName && modelsByName[selectedName]) {
                modelsByName[selectedName].forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    if (model === localStorage.getItem('giftModelFilter')) {
                        option.selected = true;
                    }
                    modelSelect.appendChild(option);
                });
            }
        }

        // Обработчик изменения имени
        function handleNameChange() {
            const nameSelect = document.getElementById('name');
            const selectedName = nameSelect.value;
            
            // Сохраняем выбранное имя
            localStorage.setItem('giftNameFilter', selectedName);
            
            // Сбрасываем модель при изменении имени
            localStorage.removeItem('giftModelFilter');
            
            // Обновляем список моделей
            updateModels();
            
            // Применяем фильтр
            applyFilters();
        }

        // Обработчик изменения модели
        function handleModelChange() {
            const modelSelect = document.getElementById('model');
            const selectedModel = modelSelect.value;
            
            // Сохраняем выбранную модель
            localStorage.setItem('giftModelFilter', selectedModel);
            
            // Применяем фильтр
            applyFilters();
        }

        // Функция для применения фильтров
        function applyFilters() {
            const name = document.getElementById('name').value;
            const model = document.getElementById('model').value;
            const params = new URLSearchParams();
            
            if (name) params.append('name', name);
            if (model) params.append('model', model);
            
            window.location.href = '{{ route('gifts.index') }}' + (params.toString() ? '?' + params.toString() : '');
        }

        // Функция для очистки фильтров
        function clearFilters(event) {
            event.preventDefault();
            localStorage.removeItem('giftNameFilter');
            localStorage.removeItem('giftModelFilter');
            window.location.href = '{{ route('gifts.index') }}';
        }

        // Восстановление фильтров при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            const savedName = localStorage.getItem('giftNameFilter');
            const savedModel = localStorage.getItem('giftModelFilter');
            
            if (savedName) {
                document.getElementById('name').value = savedName;
                updateModels();
            }

            // Если есть сохраненные фильтры, но нет параметров в URL, применяем их
            if ((savedName || savedModel) && !window.location.search) {
                isInitialLoad = false;
                applyFilters();
            }

            isInitialLoad = false;
        });
    </script>
</x-app-layout>
