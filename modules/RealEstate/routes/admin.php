<?php

use App\Modules\RealEstate\Http\Controllers\Admin\InquiryController;
use App\Modules\RealEstate\Http\Controllers\Admin\PropertyController;
use App\Modules\RealEstate\Http\Controllers\Admin\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->prefix('admin/real-estate')->name('admin.real-estate.')->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::get('taxonomies/{taxonomy}', [TaxonomyController::class, 'index'])->name('taxonomies.index');
    Route::post('taxonomies/{taxonomy}', [TaxonomyController::class, 'store'])->name('taxonomies.store');
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
});
