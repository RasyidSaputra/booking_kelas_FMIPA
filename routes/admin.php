<?php

use App\Http\Controllers\Admin\BookingReviewController;
use App\Http\Controllers\Admin\FacilityManagementController;
use App\Http\Controllers\Admin\LectureScheduleController;
use App\Http\Controllers\Admin\LectureScheduleImportController;
use App\Http\Controllers\Admin\RoomManagementController;
use App\Http\Controllers\Admin\RoomMasterController;
use App\Http\Controllers\Admin\ScheduleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('can:view-room-master')->group(function () {
    Route::get('/rooms', RoomMasterController::class)->name('rooms.index');

    Route::get('/lecture-schedules', [LectureScheduleController::class, 'index'])->name('lecture-schedules.index');
    Route::get('/lecture-schedules/import', [LectureScheduleImportController::class, 'index'])->name('lecture-schedules.import.index');
    Route::get('/lecture-schedules/import/template', [LectureScheduleImportController::class, 'template'])->name('lecture-schedules.import.template');
    Route::post('/lecture-schedules/import/preview', [LectureScheduleImportController::class, 'preview'])->name('lecture-schedules.import.preview');
    Route::post('/lecture-schedules/import/confirm', [LectureScheduleImportController::class, 'confirm'])->name('lecture-schedules.import.confirm');
    Route::post('/lecture-schedules', [LectureScheduleController::class, 'store'])->name('lecture-schedules.store');
    Route::patch('/lecture-schedules/{lectureSchedule}', [LectureScheduleController::class, 'update'])->whereNumber('lectureSchedule')->name('lecture-schedules.update');
    Route::patch('/lecture-schedules/{lectureSchedule}/cancel', [LectureScheduleController::class, 'cancel'])->whereNumber('lectureSchedule')->name('lecture-schedules.cancel');

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
