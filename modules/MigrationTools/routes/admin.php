<?php

use App\Modules\MigrationTools\Http\Controllers\Admin\MigrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/migration')->name('admin.migration.')->group(function () {
    Route::get('/profiles', [MigrationController::class, 'profiles'])->name('profiles');
    Route::post('/profiles', [MigrationController::class, 'storeProfile'])->name('profiles.store');

    Route::get('/runs', [MigrationController::class, 'runs'])->name('runs');
    Route::post('/run', [MigrationController::class, 'runFromProfile'])->name('run');
    Route::get('/runs/{run}', [MigrationController::class, 'showRun'])->name('runs.show');
    Route::post('/runs/{run}/retry-failed', [MigrationController::class, 'retryFailed'])->name('runs.retry-failed');
    Route::post('/runs/{run}/rollback', [MigrationController::class, 'rollbackRun'])->name('runs.rollback');
    Route::post('/items/{item}/rollback', [MigrationController::class, 'rollbackItem'])->name('items.rollback');

    Route::post('/wizard/csv-mapping', [MigrationController::class, 'csvMappingWizard'])->name('wizard.csv-mapping');
    Route::get('/exports', [MigrationController::class, 'exports'])->name('exports');

    Route::get('/', [MigrationController::class, 'runs'])->name('index');
});
