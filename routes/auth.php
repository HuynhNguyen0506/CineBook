<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;   

//Login
Route::get('/login', [AuthController::class, 'login']) -> name('login');
Route::post('/login', [AuthController::class, 'submitLogin']) -> name('submitLogin');

//Forgot Password
Route::get('/forgot-password', [AuthController::class, 'passRequest']) -> name('password.request');
Route::post('/forgot-password', [AuthController::class, 'passEmail']) -> name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'passReset']) -> name('password.reset');
Route::post('/reset-password', [AuthController::class, 'passUpdate']) -> name('password.update');

//Register
Route::get('/register', [AuthController::class, 'register']) -> name('register');
Route::post('/register', [AuthController::class, 'submitRegister']) -> name('submitRegister');