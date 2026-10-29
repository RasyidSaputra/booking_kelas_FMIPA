<?php

use App\Http\Controllers\Admin\RoomMasterController;
use App\Http\Controllers\Admin\RoomManagementController;
use App\Http\Controllers\Admin\FacilityManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ScheduleManagementController;
use App\Http\Controllers\Admin\BookingReviewController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->whereNumber('room')->name('rooms.show');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->whereNumber('booking')->name('bookings.update');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->whereNumber('booking')->name('bookings.cancel');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->whereNumber('booking')->name('bookings.destroy');
    Route::prefix('admin')->name('admin.')->middleware('can:view-room-master')->group(function () {
        Route::get('/rooms', RoomMasterController::class)->name('rooms.index');
        Route::post('/rooms', [RoomManagementController::class, 'store'])->name('rooms.store');
        Route::patch('/rooms/{room}', [RoomManagementController::class, 'update'])->whereNumber('room')->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomManagementController::class, 'destroy'])->whereNumber('room')->name('rooms.destroy');
        Route::post('/facilities', [FacilityManagementController::class, 'store'])->name('facilities.store');
        Route::patch('/facilities/{facility}', [FacilityManagementController::class, 'update'])->whereNumber('facility')->name('facilities.update');
        Route::delete('/facilities/{facility}', [FacilityManagementController::class, 'destroy'])->whereNumber('facility')->name('facilities.destroy');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserManagementController::class, 'update'])->whereNumber('user')->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->whereNumber('user')->name('users.destroy');
        Route::post('/schedules', [ScheduleManagementController::class, 'store'])->name('schedules.store');
        Route::patch('/schedules/{booking}', [ScheduleManagementController::class, 'update'])->whereNumber('booking')->name('schedules.update');
        Route::patch('/schedules/{booking}/cancel', [ScheduleManagementController::class, 'cancel'])->whereNumber('booking')->name('schedules.cancel');
        Route::patch('/bookings/{booking}/review', BookingReviewController::class)->whereNumber('booking')->name('bookings.review');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
