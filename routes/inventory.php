<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    // load the page for inventory functionality
    Route::view('inventory', 'inventory')
        ->name('inventory');
});
