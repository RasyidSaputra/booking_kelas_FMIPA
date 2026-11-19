<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/**
 * Menampilkan halaman login pada alamat awal aplikasi.
 * Konsep Laravel: Routing Web dan Route Closure.
 */
Route::get('/', function () {
    return view('auth/login');
});

/**
 * Mengelompokkan halaman utama yang melewati middleware login dan verifikasi.
 * Konsep Laravel: Route Group, Authentication Middleware dan Named Routes.
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->whereNumber('room')->name('rooms.show');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])
        ->whereNumber('booking')
        ->name('bookings.show');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->whereNumber('booking')->name('bookings.update');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->whereNumber('booking')->name('bookings.cancel');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->whereNumber('booking')->name('bookings.destroy');

    require __DIR__.'/admin.php';
});

/**
 * Mengelompokkan route pengelolaan profil yang membutuhkan sesi login.
 * Konsep Laravel: Route Group dan Authentication Middleware.
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
