<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TesterCustomerController;
use App\Http\Controllers\Api\TesterController;
use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\MaintenanceScheduleController;
use App\Http\Controllers\Api\CalibrationScheduleController;
use App\Http\Controllers\Api\EventLogController;
use App\Http\Controllers\Api\SparePartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Public routes - Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', function (Request $request) {
        return response()->json([
            'success' => false,
            'message' => 'Registration not implemented yet',
            'code' => 501,
        ], 501);
    });

    // Protected routes - Require authentication
    Route::middleware('auth:sanctum')->group(function () {
        // Authentication
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Customers
        Route::apiResource('customers', TesterCustomerController::class);

        // Testers
        Route::apiResource('testers', TesterController::class);
        Route::patch('/testers/{tester}/status', [TesterController::class, 'updateStatus']);

        // Fixtures
        Route::apiResource('fixtures', FixtureController::class);

        // Maintenance Schedules
        Route::apiResource('maintenance-schedules', MaintenanceScheduleController::class);
        Route::post('/maintenance-schedules/{schedule}/complete', [MaintenanceScheduleController::class, 'complete']);

        // Calibration Schedules
        Route::apiResource('calibration-schedules', CalibrationScheduleController::class);
        Route::post('/calibration-schedules/{schedule}/complete', [CalibrationScheduleController::class, 'complete']);

        // Event Logs
        Route::apiResource('event-logs', EventLogController::class, ['only' => ['index', 'store', 'show']]);

        // Spare Parts
        Route::apiResource('spare-parts', SparePartController::class);
    });
});

// Default Laravel API health check
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
