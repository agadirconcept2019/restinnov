<?php

use App\Modules\Communications\Http\Controllers\Admin\EmailLogController;
use App\Modules\Communications\Http\Controllers\Admin\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/communications')->name('admin.communications.')->group(function () {
    Route::get('/templates', [TemplateController::class, 'index'])->middleware('permission:communications.templates.manage')->name('templates.index');
    Route::post('/templates', [TemplateController::class, 'store'])->middleware('permission:communications.templates.manage')->name('templates.store');
    Route::post('/templates/preview', [TemplateController::class, 'preview'])->middleware(['permission:communications.templates.manage', 'throttle:forms-public'])->name('templates.preview');
    Route::post('/templates/send-test', [TemplateController::class, 'sendTest'])->middleware(['permission:communications.templates.manage', 'throttle:forms-public'])->name('templates.send-test');

    Route::get('/logs', [EmailLogController::class, 'index'])->middleware('permission:communications.logs.view')->name('logs.index');
    Route::get('/logs/{log}', [EmailLogController::class, 'show'])->middleware('permission:communications.logs.view')->name('logs.show');
    Route::post('/logs/{log}/retry', [EmailLogController::class, 'retry'])->middleware(['permission:communications.logs.retry', 'throttle:forms-public'])->name('logs.retry');
    Route::post('/logs/bulk', [EmailLogController::class, 'bulk'])->middleware(['permission:communications.logs.retry', 'throttle:forms-public'])->name('logs.bulk');
});
