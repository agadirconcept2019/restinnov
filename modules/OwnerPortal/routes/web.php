<?php

use App\Modules\OwnerPortal\Http\Controllers\Owner\BookingRequestController;
use App\Modules\OwnerPortal\Http\Controllers\Owner\DashboardController;
use App\Modules\OwnerPortal\Http\Controllers\Owner\InquiryController;
use App\Modules\OwnerPortal\Http\Controllers\Owner\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'set.locale'])->group(function () {
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
        Route::get('/properties/{property}/calendar', [PropertyController::class, 'calendar'])->name('properties.calendar');
        Route::post('/properties/{property}/calendar/bulk-update', [PropertyController::class, 'bulkUpdate'])->name('properties.calendar.bulk-update');

        Route::get('/booking-requests', [BookingRequestController::class, 'index'])->name('bookings.index');
        Route::get('/booking-requests/{bookingRequest}', [BookingRequestController::class, 'show'])->name('bookings.show');
        Route::post('/booking-requests/{bookingRequest}/status', [BookingRequestController::class, 'status'])->middleware('throttle:forms-public')->name('bookings.status');
        Route::post('/booking-requests/{bookingRequest}/notes', [BookingRequestController::class, 'note'])->name('bookings.notes.store');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
        Route::post('/inquiries/{inquiry}/notes', [InquiryController::class, 'note'])->name('inquiries.notes.store');
        Route::post('/inquiries/{inquiry}/reply', [InquiryController::class, 'reply'])->middleware('throttle:forms-public')->name('inquiries.reply');
    });

    Route::prefix('{locale}/owner')->whereIn('locale', config('locales.supported', ['en', 'fr', 'es']))->group(function () {
        Route::get('/', DashboardController::class);
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/booking-requests', [BookingRequestController::class, 'index']);
        Route::get('/inquiries', [InquiryController::class, 'index']);
    });
});
