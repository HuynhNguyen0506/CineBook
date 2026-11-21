<?php

// Kết nối các routes vào file web.php chính
require __DIR__.'/client.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
