<?php

use App\Modules\CmsPages\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/cms-pages')->name('admin.cms-pages.')->group(function () {
    Route::get('pages', [PageController::class, 'index'])->middleware('permission:cms.pages.view')->name('pages.index');
    Route::get('pages/create', [PageController::class, 'create'])->middleware('permission:cms.pages.create')->name('pages.create');
    Route::post('pages', [PageController::class, 'store'])->middleware('permission:cms.pages.create')->name('pages.store');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->middleware('permission:cms.pages.update')->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->middleware('permission:cms.pages.update')->name('pages.update');
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->middleware('permission:cms.pages.delete')->name('pages.destroy');
});
