<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    // load the page for issues-specific functionality
    Route::view('services', 'services')
        ->name('services');
});
