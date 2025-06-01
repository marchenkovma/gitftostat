<?php

use App\Http\Controllers\GiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello World';
});

Route::get('/gifts', [GiftController::class, 'index'])->name('gifts.index');
Route::get('/gifts/{gift}', [GiftController::class, 'show'])->name('gifts.show');
