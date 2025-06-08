<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-medium text-white">{{ __('Gift list') }}</h1>
        <a href="{{ route('favorites.index') }}" class="text-blue-400 hover:text-blue-300">
            {{ __('Favorites Gift')}}
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @foreach($gifts as $gift)
            <x-gift-card :gift="$gift" />
        @endforeach
    </div>

    <div class="mt-6">
        {{ $gifts->links() }}
    </div>
</x-app-layout>
