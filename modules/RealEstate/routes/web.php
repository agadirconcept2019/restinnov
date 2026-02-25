<?php

use App\Modules\RealEstate\Http\Controllers\Public\BookingRequestPublicController;
use App\Modules\RealEstate\Http\Controllers\Public\InquiryPublicController;
use App\Modules\RealEstate\Http\Controllers\Public\PropertyIcsController;
use App\Modules\RealEstate\Http\Controllers\Client\ClientAccessController;
use App\Modules\RealEstate\Http\Controllers\Public\PropertyPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','set.locale'])->group(function () {
    Route::get('/our-properties', [PropertyPublicController::class, 'index'])->name('realestate.properties.index');
    Route::get('/properties/{slug}', [PropertyPublicController::class, 'show'])->name('realestate.properties.show');
    Route::get('/properties/{slug}/calendar.ics', [PropertyIcsController::class, 'show'])->name('realestate.properties.calendar');
    Route::get('/listings/{typeSlug}', [PropertyPublicController::class, 'type'])->name('realestate.archive.type');
    Route::get('/action/{modeSlug}', [PropertyPublicController::class, 'mode'])->name('realestate.archive.mode');
    Route::get('/city/{citySlug}', [PropertyPublicController::class, 'city'])->name('realestate.archive.city');
    Route::get('/area/{areaSlug}', [PropertyPublicController::class, 'area'])->name('realestate.archive.area');
    Route::post('/properties/{property}/inquiry', [InquiryPublicController::class, 'store'])->name('realestate.inquiry.store');
    Route::post('/properties/{property}/booking-request', [BookingRequestPublicController::class, 'store'])->middleware('throttle:forms-public')->name('realestate.booking-request.store');

    Route::prefix('{locale}')->middleware('set.locale')->whereIn('locale', config('locales.supported', ['en','fr','es']))->group(function () {
        Route::get('/our-properties', [PropertyPublicController::class, 'index'])->name('realestate.properties.index.localized');
        Route::get('/properties/{slug}', [PropertyPublicController::class, 'show'])->name('realestate.properties.show.localized');
        Route::get('/properties/{slug}/calendar.ics', [PropertyIcsController::class, 'show']);
        Route::get('/listings/{typeSlug}', [PropertyPublicController::class, 'type']);
        Route::get('/action/{modeSlug}', [PropertyPublicController::class, 'mode']);
        Route::get('/city/{citySlug}', [PropertyPublicController::class, 'city']);
        Route::get('/area/{areaSlug}', [PropertyPublicController::class, 'area']);
    });
});


Route::middleware(['web', 'signed', 'throttle:forms-public'])->group(function () {
    Route::get('/client/bookings/{booking}/summary', [ClientAccessController::class, 'bookingSummary'])->name('realestate.client.booking.summary');
    Route::get('/client/invoices/{invoice}/download', [ClientAccessController::class, 'invoiceDownload'])->name('realestate.client.invoice.download');
});
