<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('scan', 'scan')
    ->middleware(['auth'])
    ->name('scan');

require __DIR__ . '/auth.php';
require __DIR__ . '/userRoles.php';
require __DIR__ . '/testers.php';
require __DIR__ . '/fixtures.php';
require __DIR__ . '/issues.php';
require __DIR__ . '/services.php';
