<?php

use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Chat route - uses direct Socket.IO communication
Route::get('/chat', function () {
    return view('chat');
})->name('chat');
