<?php

use App\Http\Controllers\Admin\Access\PermissionController;
use App\Http\Controllers\Admin\Access\RoleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Core\MediaController;
use App\Http\Controllers\Admin\Core\MenuController;
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

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view')->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->middleware('permission:audit_logs.view')->name('audit-logs.show');

        Route::get('/redirects', [RedirectController::class, 'index'])->middleware('permission:redirects.manage')->name('redirects.index');
        Route::post('/redirects', [RedirectController::class, 'store'])->middleware('permission:redirects.manage')->name('redirects.store');
        Route::put('/redirects/{redirect}', [RedirectController::class, 'update'])->middleware('permission:redirects.manage')->name('redirects.update');

        Route::get('/ops/health', [OpsController::class, 'health'])->middleware('permission:ops.view')->name('ops.health');
        Route::get('/ops/jobs', [OpsController::class, 'jobs'])->middleware('permission:ops.view')->name('ops.jobs');
        Route::post('/ops/jobs/retry', [OpsController::class, 'retryJob'])->middleware(['permission:ops.jobs.retry', 'throttle:forms-public'])->name('ops.jobs.retry');

        Route::get('/access/roles', [RoleController::class, 'index'])->middleware('permission:users.manage')->name('access.roles.index');
        Route::post('/access/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:users.manage')->name('access.roles.permissions.update');
        Route::post('/access/users/{user}/roles', [RoleController::class, 'assignRole'])->middleware('permission:users.manage')->name('access.users.roles.update');
        Route::get('/access/permissions', [PermissionController::class, 'index'])->middleware('permission:users.manage')->name('access.permissions.index');

        Route::get('/media', [MediaController::class, 'index'])->middleware('permission:media.manage')->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->middleware('permission:media.manage')->name('media.store');
        Route::put('/media/{media}', [MediaController::class, 'update'])->middleware('permission:media.manage')->name('media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->middleware('permission:media.manage')->name('media.destroy');

        Route::get('/menus', [MenuController::class, 'index'])->middleware('permission:menus.manage')->name('menus.index');
        Route::post('/menus/{menu}/reorder', [MenuController::class, 'reorder'])->middleware('permission:menus.manage')->name('menus.reorder');
    });
});
