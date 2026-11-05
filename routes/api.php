<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:api-login')->name('login');

    Route::middleware(['auth:sanctum', 'verified', 'throttle:api'])->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('rooms/{room}/availability', [RoomController::class, 'availability'])->whereNumber('room')->name('rooms.availability');
        Route::get('rooms/{room}', [RoomController::class, 'show'])->whereNumber('room')->name('rooms.show');
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->whereNumber('booking')->name('bookings.show');

        Route::prefix('admin')->name('admin.')->middleware('can:view-room-master')->group(function () {
            Route::get('bookings', [BookingController::class, 'adminIndex'])->name('bookings.index');
            Route::patch('bookings/{booking}/review', [BookingController::class, 'review'])->whereNumber('booking')->name('bookings.review');
        });
    });
});
