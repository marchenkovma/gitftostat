<?php

use App\Http\Controllers\GiftController;
use App\Http\Controllers\FavoriteGiftsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/', '/gifts');

// Отладочный маршрут для проверки параметров
Route::get('/debug-params', function (Request $request) {
    Log::info('Debug params:', [
        'all' => $request->all(),
        'query' => $request->query(),
        'name' => $request->name,
        'model' => $request->model,
        'url' => $request->url(),
        'full_url' => $request->fullUrl()
    ]);
    return response()->json($request->all());
});

Route::resource('gifts', GiftController::class);
Route::get('/favorites', [FavoriteGiftsController::class, 'index'])->name('favorites.index');
