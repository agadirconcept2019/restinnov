<?php

use App\Modules\MigrationTools\Http\Controllers\Admin\MigrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/migration')->name('admin.migration.')->group(function () {
    Route::get('/', [MigrationController::class, 'index'])->name('index');
    Route::post('/', [MigrationController::class, 'store'])->name('store');
    Route::get('/exports', [MigrationController::class, 'exports'])->name('exports');
    Route::get('/{migration}', [MigrationController::class, 'show'])->name('show');
    Route::post('/{migration}/retry-failed', [MigrationController::class, 'retryFailed'])->name('retry-failed');
});
