<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    Route::get('calendar-events', [CalendarController::class, 'index'])
        ->name('calendar.events');
});