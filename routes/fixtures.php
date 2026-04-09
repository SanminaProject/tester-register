<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    // load the page for fixture-specific functionality
    Route::view('fixtures', 'fixtures')
        ->name('fixtures');
});
