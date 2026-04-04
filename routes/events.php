<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Api\CalendarController;

Route::middleware(['auth'])->group(function () {
    Route::get('calendar-events', [CalendarController::class, 'index'])
        ->name('calendar.events');
});