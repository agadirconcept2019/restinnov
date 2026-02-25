<?php

use App\Modules\RealEstate\Http\Controllers\Public\InquiryPublicController;
use App\Modules\RealEstate\Http\Controllers\Public\PropertyPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/our-properties', [PropertyPublicController::class, 'index'])->name('realestate.properties.index');
    Route::get('/properties/{slug}', [PropertyPublicController::class, 'show'])->name('realestate.properties.show');
    Route::get('/listings/{typeSlug}', [PropertyPublicController::class, 'type'])->name('realestate.archive.type');
    Route::get('/action/{modeSlug}', [PropertyPublicController::class, 'mode'])->name('realestate.archive.mode');
    Route::get('/city/{citySlug}', [PropertyPublicController::class, 'city'])->name('realestate.archive.city');
    Route::get('/area/{areaSlug}', [PropertyPublicController::class, 'area'])->name('realestate.archive.area');
    Route::post('/properties/{property}/inquiry', [InquiryPublicController::class, 'store'])->name('realestate.inquiry.store');

    Route::prefix('{locale}')->whereIn('locale', config('locales.supported', ['en','fr','es']))->group(function () {
        Route::get('/our-properties', [PropertyPublicController::class, 'index'])->name('realestate.properties.index.localized');
        Route::get('/properties/{slug}', [PropertyPublicController::class, 'show'])->name('realestate.properties.show.localized');
        Route::get('/listings/{typeSlug}', [PropertyPublicController::class, 'type']);
        Route::get('/action/{modeSlug}', [PropertyPublicController::class, 'mode']);
        Route::get('/city/{citySlug}', [PropertyPublicController::class, 'city']);
        Route::get('/area/{areaSlug}', [PropertyPublicController::class, 'area']);
    });
});
