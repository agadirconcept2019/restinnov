<?php

use App\Http\Controllers\Core\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/core-health', fn () => response()->json(['ok' => true]))->name('core.health');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap.xml');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});
