<?php

use App\Modules\Communications\Http\Controllers\Admin\EmailLogController;
use App\Modules\Communications\Http\Controllers\Admin\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/communications')->name('admin.communications.')->group(function () {
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
    Route::post('/templates/preview', [TemplateController::class, 'preview'])->middleware('throttle:forms-public')->name('templates.preview');
    Route::post('/templates/send-test', [TemplateController::class, 'sendTest'])->middleware('throttle:forms-public')->name('templates.send-test');

    Route::get('/logs', [EmailLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [EmailLogController::class, 'show'])->name('logs.show');
    Route::post('/logs/{log}/retry', [EmailLogController::class, 'retry'])->middleware('throttle:forms-public')->name('logs.retry');
});
