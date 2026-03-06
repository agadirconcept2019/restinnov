<?php

use App\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','install.guard'])->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'step1'])->name('step1');

    Route::get('/database', [InstallController::class, 'step2'])->name('step2');
    Route::post('/database', [InstallController::class, 'storeStep2'])->name('step2.store');

    Route::get('/app', [InstallController::class, 'step3'])->name('step3');
    Route::post('/app', [InstallController::class, 'storeStep3'])->name('step3.store');

    Route::get('/system', [InstallController::class, 'step4'])->name('step4');

    Route::get('/admin', [InstallController::class, 'step5'])->name('step5');
    Route::post('/admin', [InstallController::class, 'storeStep5'])->name('step5.store');

    Route::get('/finalize', [InstallController::class, 'step6'])->name('step6');
});
