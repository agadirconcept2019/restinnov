<?php

use App\Modules\RealEstate\Http\Controllers\Admin\AvailabilityController;
use App\Modules\RealEstate\Http\Controllers\Admin\BookingController;
use App\Modules\RealEstate\Http\Controllers\Admin\BookingRequestController;
use App\Modules\RealEstate\Http\Controllers\Admin\IcalFeedController;
use App\Modules\RealEstate\Http\Controllers\Admin\InquiryController;
use App\Modules\RealEstate\Http\Controllers\Admin\PropertyController;
use App\Modules\RealEstate\Http\Controllers\Admin\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/real-estate')->name('admin.real-estate.')->group(function () {

    Route::get('properties', [PropertyController::class, 'index'])->middleware('permission:realestate.properties.view')->name('properties.index');
    Route::get('properties/create', [PropertyController::class, 'create'])->middleware('permission:realestate.properties.create')->name('properties.create');
    Route::post('properties', [PropertyController::class, 'store'])->middleware('permission:realestate.properties.create')->name('properties.store');
    Route::post('properties/bulk', [PropertyController::class, 'bulk'])->middleware('permission:realestate.properties.update')->name('properties.bulk');
    Route::get('properties/{property}', [PropertyController::class, 'show'])->middleware('permission:realestate.properties.view')->name('properties.show');
    Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->middleware('permission:realestate.properties.update')->name('properties.edit');
    Route::put('properties/{property}', [PropertyController::class, 'update'])->middleware('permission:realestate.properties.update')->name('properties.update');
    Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->middleware('permission:realestate.properties.delete')->name('properties.destroy');

    Route::get('taxonomies/{taxonomy}', [TaxonomyController::class, 'index'])->middleware('permission:realestate.properties.update')->name('taxonomies.index');
    Route::post('taxonomies/{taxonomy}', [TaxonomyController::class, 'store'])->middleware('permission:realestate.properties.update')->name('taxonomies.store');
    Route::get('inquiries', [InquiryController::class, 'index'])->middleware('permission:realestate.bookings.manage')->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->middleware('permission:realestate.bookings.manage')->name('inquiries.show');

    Route::get('availability', [AvailabilityController::class, 'index'])->middleware('permission:realestate.availability.manage')->name('availability.index');
    Route::post('availability/bulk-update', [AvailabilityController::class, 'bulkUpdate'])->middleware('permission:realestate.availability.manage')->name('availability.bulk-update');

    Route::get('booking-requests', [BookingRequestController::class, 'index'])->middleware('permission:realestate.bookings.manage')->name('booking-requests.index');
    Route::post('booking-requests/bulk', [BookingRequestController::class, 'bulk'])->middleware('permission:realestate.bookings.manage')->name('booking-requests.bulk');
    Route::get('booking-requests/{bookingRequest}', [BookingRequestController::class, 'show'])->middleware('permission:realestate.bookings.manage')->name('booking-requests.show');
    Route::post('booking-requests/{bookingRequest}/status', [BookingRequestController::class, 'updateStatus'])->middleware('permission:realestate.bookings.manage')->name('booking-requests.status');

    Route::get('bookings', [BookingController::class, 'index'])->middleware('permission:realestate.bookings.manage')->name('bookings.index');
    Route::post('bookings/bulk', [BookingController::class, 'bulk'])->middleware('permission:realestate.bookings.manage')->name('bookings.bulk');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->middleware('permission:realestate.bookings.manage')->name('bookings.show');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('permission:realestate.bookings.manage')->name('bookings.cancel');
    Route::post('bookings/{booking}/resend', [BookingController::class, 'resend'])->middleware(['permission:realestate.bookings.manage', 'throttle:forms-public'])->name('bookings.resend');
    Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->middleware('permission:realestate.bookings.manage')->name('bookings.invoice');
    Route::get('bookings/{booking}/invoice/download', [BookingController::class, 'invoiceDownload'])->middleware('permission:realestate.bookings.manage')->name('bookings.invoice.download');

    Route::get('ical-feeds', [IcalFeedController::class, 'index'])->middleware('permission:realestate.ical.manage')->name('ical-feeds.index');
    Route::post('ical-feeds', [IcalFeedController::class, 'store'])->middleware('permission:realestate.ical.manage')->name('ical-feeds.store');
    Route::put('ical-feeds/{icalFeed}', [IcalFeedController::class, 'update'])->middleware('permission:realestate.ical.manage')->name('ical-feeds.update');
    Route::delete('ical-feeds/{icalFeed}', [IcalFeedController::class, 'destroy'])->middleware('permission:realestate.ical.manage')->name('ical-feeds.destroy');
    Route::post('ical-feeds/{icalFeed}/sync-now', [IcalFeedController::class, 'syncNow'])->middleware('permission:realestate.ical.manage')->name('ical-feeds.sync-now');
});
