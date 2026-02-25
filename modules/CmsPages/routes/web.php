<?php

use App\Modules\CmsPages\Http\Controllers\Public\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','set.locale'])->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('cms.home');
    Route::get('/our-services', [PageController::class, 'services'])->name('cms.services');
    Route::get('/rd', [PageController::class, 'rd'])->name('cms.rd');
    Route::get('/faq', [PageController::class, 'faq'])->name('cms.faq');
    Route::get('/contact-us', [PageController::class, 'contact'])->name('cms.contact');
    Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('cms.terms');

    Route::prefix('{locale}')->middleware('set.locale')->whereIn('locale', config('locales.supported', ['en','fr','es']))->group(function () {
        Route::get('/', [PageController::class, 'home']);
        Route::get('/our-services', [PageController::class, 'services']);
        Route::get('/rd', [PageController::class, 'rd']);
        Route::get('/faq', [PageController::class, 'faq']);
        Route::get('/contact-us', [PageController::class, 'contact']);
        Route::get('/terms-and-conditions', [PageController::class, 'terms']);
    });
});
