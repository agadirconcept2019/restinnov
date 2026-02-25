<?php

use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\V1\Admin\OperationsAdminApiController;
use App\Http\Controllers\Api\V1\Admin\PropertyAdminApiController;
use App\Http\Controllers\Api\V1\Owner\OwnerApiController;
use App\Http\Controllers\Api\V1\Public\BlogPublicApiController;
use App\Http\Controllers\Api\V1\Public\PagePublicApiController;
use App\Http\Controllers\Api\V1\Public\PropertyPublicApiController;
use App\Http\Controllers\Api\V1\Support\SupportApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:api-auth')->group(function () {
    Route::post('/token', [TokenController::class, 'issue']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [TokenController::class, 'logout']);
});

Route::prefix('v1')->middleware('throttle:api-public')->group(function () {
    Route::get('/properties', [PropertyPublicApiController::class, 'index']);
    Route::get('/properties/{slug}', [PropertyPublicApiController::class, 'show']);
    Route::get('/blog/posts', [BlogPublicApiController::class, 'index']);
    Route::get('/blog/posts/{slug}', [BlogPublicApiController::class, 'show']);
    Route::get('/pages/{slug}', [PagePublicApiController::class, 'show']);
});

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api-private'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/properties', [PropertyAdminApiController::class, 'index'])->middleware('permission:realestate.properties.view');
        Route::post('/properties', [PropertyAdminApiController::class, 'store'])->middleware('permission:realestate.properties.create');
        Route::patch('/properties/{property}', [PropertyAdminApiController::class, 'update'])->middleware('permission:realestate.properties.update');

        Route::post('/availability/bulk', [OperationsAdminApiController::class, 'bulkAvailability'])->middleware('permission:realestate.availability.manage');

        Route::get('/bookings', [OperationsAdminApiController::class, 'bookings'])->middleware('permission:realestate.bookings.manage');
        Route::patch('/bookings/{booking}', [OperationsAdminApiController::class, 'updateBooking'])->middleware('permission:realestate.bookings.manage');

        Route::get('/booking-requests', [OperationsAdminApiController::class, 'bookingRequests'])->middleware('permission:realestate.bookings.manage');
        Route::patch('/booking-requests/{bookingRequest}', [OperationsAdminApiController::class, 'updateBookingRequest'])->middleware('permission:realestate.bookings.manage');

        Route::get('/ical-feeds', [OperationsAdminApiController::class, 'icalFeeds'])->middleware('permission:realestate.ical.manage');
        Route::patch('/ical-feeds/{icalFeed}', [OperationsAdminApiController::class, 'updateIcalFeed'])->middleware('permission:realestate.ical.manage');
    });

    Route::prefix('owner')->middleware('permission:role:owner')->group(function () {
        Route::get('/properties', [OwnerApiController::class, 'properties']);
        Route::get('/bookings', [OwnerApiController::class, 'bookings']);
        Route::get('/inquiries', [OwnerApiController::class, 'inquiries']);
        Route::patch('/bookings/{booking}/status', [OwnerApiController::class, 'updateBookingStatus']);
    });

    Route::prefix('support')->group(function () {
        Route::get('/forms/submissions', [SupportApiController::class, 'formSubmissions'])->middleware('permission:forms.submissions.view');
        Route::get('/communications/email-logs', [SupportApiController::class, 'emailLogs'])->middleware('permission:communications.logs.view');
    });
});
