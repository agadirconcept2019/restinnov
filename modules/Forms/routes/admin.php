<?php

use App\Modules\Forms\Http\Controllers\Admin\FormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/forms')->name('admin.forms.')->group(function () {
    Route::get('/submissions', [FormSubmissionController::class, 'index'])->middleware('permission:forms.submissions.view')->name('submissions.index');
    Route::get('/submissions/{submission}', [FormSubmissionController::class, 'show'])->middleware('permission:forms.submissions.view')->name('submissions.show');
    Route::post('/submissions/{submission}/processed', [FormSubmissionController::class, 'markProcessed'])->middleware('permission:forms.submissions.update_status')->name('submissions.processed');
});
