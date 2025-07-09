<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// V1
Route::group(['prefix' => 'v1'], function () {

    // Dashboard
    Route::middleware('auth:sanctum')->get('/dashboard', [DashboardController::class, 'index']);

    // Authentication
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthenticationController::class, 'login'])->middleware('guest.api');
        Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware(['auth:sanctum']);
        Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
            return $request->user();
        });
    });

    // Users
    Route::group(['prefix' => 'users'], function () {
        Route::delete('/{id}', [UserController::class, 'delete']);

        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'create']);
        Route::get('/{id}', [UserController::class, 'retrieve']);
        Route::put('/{id}', [UserController::class, 'update']);
    });

    // Subscribers
    Route::group(['prefix' => 'subscribers'], function () {
        Route::get('/meter/{id}', [SubscriberController::class, 'meter']);
        Route::get('/unassigned', [SubscriberController::class, 'unassigned']);
        Route::delete('/', [SubscriberController::class, 'bulkDestroy']);
    });
    Route::apiResource('subscribers', SubscriberController::class);

    // Meters
    Route::apiResource('meters', MeterController::class);
    Route::group(['prefix' => 'meters'], function () {
        Route::put('/{id}/assign/{subscriber}', [MeterController::class, 'assign']);
        Route::put('/clear/{id}', [MeterController::class, 'clear']);
        Route::put('/{id}/status/{status}', [MeterController::class, 'status']);
    });

    // MeterReadings
    Route::group(['prefix' => 'readings'], function () {
        Route::get("/latest", [MeterReadingController::class, 'latest']);
        Route::get('/latest/meter', [MeterReadingController::class, 'latestReadingsMeter']);
        Route::post('/bulk', [MeterReadingController::class, 'storeBulk']);
    });
    Route::apiResource('readings', MeterReadingController::class);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::group(['prefix' => 'invoices'], function () {});

});
