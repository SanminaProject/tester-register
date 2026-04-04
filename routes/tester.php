<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    // load the page for tester-specific functionality
    Route::view('tester', 'tester')
        ->name('tester');
});