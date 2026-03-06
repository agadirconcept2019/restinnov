<?php

use App\Modules\OwnerPortal\Http\Controllers\Admin\OwnerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/owners', [OwnerController::class, 'index'])->middleware('permission:users.manage')->name('owners.index');
    Route::get('/owner-portal/owners', [OwnerController::class, 'index'])->middleware('permission:users.manage')->name('owner-portal.owners.index');
});
