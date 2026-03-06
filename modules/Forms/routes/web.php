<?php

use App\Modules\Forms\Http\Controllers\Public\FormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:forms-public'])->group(function () {
    Route::post('/forms/contact', [FormSubmissionController::class, 'storeContact'])->name('forms.contact.submit');
    Route::post('/forms/quote', [FormSubmissionController::class, 'storeQuote'])->name('forms.quote.submit');
});
