<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('set.locale')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::prefix('{locale}')
        ->whereIn('locale', config('locales.supported', ['en', 'fr', 'es']))
        ->group(function () {
            Route::get('/', HomeController::class)->name('home.localized');
        });
});
