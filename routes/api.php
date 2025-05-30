<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// V1
Route::group(['prefix' => 'v1'], function () {
    // Authentication
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthenticationController::class, 'login'])->middleware('guest.api');
        Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware(['auth:sanctum']);
    });

    // Users
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'create']);
        Route::get('/{id}', [UserController::class, 'retrieve']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id', [UserController::class, 'delete']);
    });

    // Subscribers
    Route::apiResource('subscribers', SubscriberController::class);
    Route::group(['prefix' => 'subscribers'], function () {
        Route::get('/meter/{id}', [SubscriberController::class, 'meter']);
    });

    // Meters
    Route::apiResource('meters', MeterController::class);
    Route::group(['prefix' => 'meters'], function () {
        Route::put('/assign/{id}/{subscriber}', [MeterController::class, 'assign']);
    });

    // MeterReadings
    Route::apiResource('readings', MeterReadingController::class);
    Route::group(['prefix' => 'readings'], function () {});

});
