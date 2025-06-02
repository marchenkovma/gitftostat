<?php

use App\Http\Controllers\GiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('gifts', GiftController::class);
