<?php

use App\Modules\MigrationTools\Http\Controllers\Admin\MigrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/migration')->name('admin.migration.')->group(function () {
    Route::get('/profiles', [MigrationController::class, 'profiles'])->middleware('permission:migration.profiles.manage')->name('profiles');
    Route::post('/profiles', [MigrationController::class, 'storeProfile'])->middleware('permission:migration.profiles.manage')->name('profiles.store');

    Route::get('/runs', [MigrationController::class, 'runs'])->middleware('permission:migration.runs.execute')->name('runs');
    Route::post('/run', [MigrationController::class, 'runFromProfile'])->middleware('permission:migration.runs.execute')->name('run');
    Route::get('/runs/{run}', [MigrationController::class, 'showRun'])->middleware('permission:migration.runs.execute')->name('runs.show');
    Route::post('/runs/{run}/retry-failed', [MigrationController::class, 'retryFailed'])->middleware('permission:migration.runs.execute')->name('runs.retry-failed');
    Route::post('/runs/{run}/rollback', [MigrationController::class, 'rollbackRun'])->middleware('permission:migration.runs.rollback')->name('runs.rollback');
    Route::post('/items/{item}/rollback', [MigrationController::class, 'rollbackItem'])->middleware('permission:migration.runs.rollback')->name('items.rollback');

    Route::post('/wizard/csv-mapping', [MigrationController::class, 'csvMappingWizard'])->middleware('permission:migration.runs.execute')->name('wizard.csv-mapping');
    Route::get('/exports', [MigrationController::class, 'exports'])->middleware('permission:migration.runs.execute')->name('exports');

    Route::get('/', [MigrationController::class, 'runs'])->middleware('permission:migration.runs.execute')->name('index');
});
