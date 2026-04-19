<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'role:Admin'])->group(function () {
    // load the page for pages only admins can access like personnel and user roles
    Route::view('admin', 'admin')
        ->name('admin');
});