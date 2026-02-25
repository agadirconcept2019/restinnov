<?php

use App\Modules\CmsPages\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/cms-pages')->name('admin.cms-pages.')->group(function () {
    Route::resource('pages', PageController::class);
});
