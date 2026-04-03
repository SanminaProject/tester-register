<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalibrationScheduleController;
use App\Http\Controllers\Api\EventLogController;
use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\MaintenanceScheduleController;
use App\Http\Controllers\Api\SparePartController;
use App\Http\Controllers\Api\TesterController;
use App\Http\Controllers\Api\TesterCustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::apiResource('customers', TesterCustomerController::class)
            ->parameters(['customers' => 'customer']);

        Route::apiResource('testers', TesterController::class)
            ->parameters(['testers' => 'tester']);
        Route::patch('testers/{tester}/status', [TesterController::class, 'updateStatus']);

        Route::apiResource('fixtures', FixtureController::class)
            ->parameters(['fixtures' => 'fixture']);

        Route::apiResource('maintenance-schedules', MaintenanceScheduleController::class)
            ->parameters(['maintenance-schedules' => 'maintenanceSchedule']);
        Route::post('maintenance-schedules/{maintenanceSchedule}/complete', [MaintenanceScheduleController::class, 'complete']);

        Route::apiResource('calibration-schedules', CalibrationScheduleController::class)
            ->parameters(['calibration-schedules' => 'calibrationSchedule']);
        Route::post('calibration-schedules/{calibrationSchedule}/complete', [CalibrationScheduleController::class, 'complete']);

        Route::apiResource('event-logs', EventLogController::class)
            ->only(['index', 'store', 'show'])
            ->parameters(['event-logs' => 'eventLog']);

        Route::get('calendar-events', [CalendarController::class, 'index']);

        Route::apiResource('spare-parts', SparePartController::class)
            ->parameters(['spare-parts' => 'sparePart']);
    });
});
