<?php

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [HomeController::class, 'home']) -> name('home');

Route::middleware(['user'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'profile']) -> name('profile');

});