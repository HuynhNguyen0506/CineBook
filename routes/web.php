<?php

// Kết nối các routes vào file web.php chính
require __DIR__.'/client.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail', function () {
    Mail::raw('This is a test email from CineBook Laravel project!', function ($message) {
        $message->to('kiamebk2023@gmail.com')
                ->subject('CineBook - Test Email');
    });

    return 'Test email sent successfully!';
});
