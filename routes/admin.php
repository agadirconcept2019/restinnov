<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OpsController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'show'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        Route::get('/redirects', [RedirectController::class, 'index'])->name('redirects.index');
        Route::post('/redirects', [RedirectController::class, 'store'])->name('redirects.store');
        Route::put('/redirects/{redirect}', [RedirectController::class, 'update'])->name('redirects.update');

        Route::get('/ops/health', [OpsController::class, 'health'])->name('ops.health');
        Route::get('/ops/jobs', [OpsController::class, 'jobs'])->name('ops.jobs');
        Route::post('/ops/jobs/retry', [OpsController::class, 'retryJob'])->middleware('throttle:forms-public')->name('ops.jobs.retry');
    });
});
