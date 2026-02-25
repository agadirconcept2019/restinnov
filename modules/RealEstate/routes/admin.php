<?php

use App\Modules\RealEstate\Http\Controllers\Admin\AvailabilityController;
use App\Modules\RealEstate\Http\Controllers\Admin\BookingController;
use App\Modules\RealEstate\Http\Controllers\Admin\BookingRequestController;
use App\Modules\RealEstate\Http\Controllers\Admin\IcalFeedController;
use App\Modules\RealEstate\Http\Controllers\Admin\InquiryController;
use App\Modules\RealEstate\Http\Controllers\Admin\PropertyController;
use App\Modules\RealEstate\Http\Controllers\Admin\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->prefix('admin/real-estate')->name('admin.real-estate.')->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::get('taxonomies/{taxonomy}', [TaxonomyController::class, 'index'])->name('taxonomies.index');
    Route::post('taxonomies/{taxonomy}', [TaxonomyController::class, 'store'])->name('taxonomies.store');
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');

    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('availability/bulk-update', [AvailabilityController::class, 'bulkUpdate'])->name('availability.bulk-update');

    Route::get('booking-requests', [BookingRequestController::class, 'index'])->name('booking-requests.index');
    Route::get('booking-requests/{bookingRequest}', [BookingRequestController::class, 'show'])->name('booking-requests.show');
    Route::post('booking-requests/{bookingRequest}/status', [BookingRequestController::class, 'updateStatus'])->name('booking-requests.status');

    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/resend', [BookingController::class, 'resend'])->middleware('throttle:forms-public')->name('bookings.resend');
    Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::get('bookings/{booking}/invoice/download', [BookingController::class, 'invoiceDownload'])->name('bookings.invoice.download');

    Route::get('ical-feeds', [IcalFeedController::class, 'index'])->name('ical-feeds.index');
    Route::post('ical-feeds', [IcalFeedController::class, 'store'])->name('ical-feeds.store');
    Route::put('ical-feeds/{icalFeed}', [IcalFeedController::class, 'update'])->name('ical-feeds.update');
    Route::delete('ical-feeds/{icalFeed}', [IcalFeedController::class, 'destroy'])->name('ical-feeds.destroy');
    Route::post('ical-feeds/{icalFeed}/sync-now', [IcalFeedController::class, 'syncNow'])->name('ical-feeds.sync-now');
});
