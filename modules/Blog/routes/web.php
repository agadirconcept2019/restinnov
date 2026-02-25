<?php

use App\Modules\Blog\Http\Controllers\Public\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'set.locale'])->group(function () {
    Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{slug}', [PostController::class, 'byCategory'])->name('blog.category');
    Route::get('/blog/{slug}', [PostController::class, 'show'])->name('blog.show');

    Route::prefix('{locale}')->middleware('set.locale')->whereIn('locale', config('locales.supported', ['en', 'fr', 'es']))->group(function () {
        Route::get('/blog', [PostController::class, 'index']);
        Route::get('/blog/category/{slug}', [PostController::class, 'byCategory']);
        Route::get('/blog/{slug}', [PostController::class, 'show']);
    });
});
