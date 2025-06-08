<?php

use App\Http\Controllers\GiftController;
use App\Http\Controllers\FavoriteGiftsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/', '/gifts');

Route::resource('gifts', GiftController::class);
Route::get('/favorites', [FavoriteGiftsController::class, 'index'])->name('favorites.index');
