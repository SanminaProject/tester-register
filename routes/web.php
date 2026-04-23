<?php

use App\Models\Tester;
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

Route::get('scan/tester/{id}', function (int $id) {
    if (!Tester::whereKey($id)->exists()) {
        return redirect()->route('scan')->with('scan_error', "Tester ID {$id} not found.");
    }

    return redirect()->route('testers', [
        'activeTab' => 'details',
        'selectedTesterId' => $id,
    ]);
})
    ->middleware(['auth'])
    ->whereNumber('id')
    ->name('scan.tester');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/testers.php';
require __DIR__ . '/fixtures.php';
require __DIR__ . '/issues.php';
require __DIR__ . '/services.php';
require __DIR__ . '/inventory.php';
