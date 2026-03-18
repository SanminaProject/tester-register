<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'role:Admin'])->group(function () {
    // load the page for user roles management
    Volt::route('user-roles', 'pages.admin.user-roles')
        ->name('user-roles');
});