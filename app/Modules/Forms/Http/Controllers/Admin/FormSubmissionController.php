<?php

namespace App\Modules\Forms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forms\FormSubmission;

class FormSubmissionController extends Controller
{
    public function index()
    {
        $submissions = FormSubmission::query()
            ->when(request('type'), fn ($q, $v) => $q->where('form_type', $v))
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('forms::admin.submissions.index', compact('submissions'));
    }

    public function show(FormSubmission $submission)
    {
        return view('forms::admin.submissions.show', compact('submission'));
    }

    public function markProcessed(FormSubmission $submission)
    {
        $submission->update(['status' => 'processed']);

        return back()->with('status', 'Submission processed.');
    }
}
