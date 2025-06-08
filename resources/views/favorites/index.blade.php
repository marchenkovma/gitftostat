<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-medium text-white">{{ __('Favorite gifts') }}</h1>
            <a href="{{ route('gifts.index') }}" class="text-blue-400 hover:text-blue-300">
                {{ __('All gifts') }}
            </a>
        </div>
        @if($favoriteGifts->isNotEmpty())
            <div class="text-lg font-medium text-white">
                {{ __('Total price') }}: <span class="text-green-400">{{ number_format($totalPrice, 2) }} {{ __('TON') }}</span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse($favoriteGifts as $gift)
            <x-gift-card :gift="$gift" />
        @empty
            <div class="col-span-full text-center text-gray-400 dark:text-gray-500 py-8">
                {{ __('No favorite gifts yet') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $favoriteGifts->links() }}
    </div>
</x-app-layout> 