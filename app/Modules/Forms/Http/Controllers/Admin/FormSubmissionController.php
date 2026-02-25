<?php

namespace App\Modules\Forms\Http\Controllers\Admin;

use App\Core\AdminTable\AdminTableQuery;
use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Forms\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(AdminTableQuery $adminTableQuery)
    {
        $query = FormSubmission::query();
        $submissions = $adminTableQuery->apply($query, [
            'search' => ['form_type', 'status', 'locale'],
            'filters' => [
                'type' => fn ($q, $v) => $q->where('form_type', $v),
                'status' => fn ($q, $v) => $q->where('status', $v),
            ],
            'sorts' => ['id', 'created_at', 'status', 'form_type'],
            'default_sort' => 'created_at',
            'default_dir' => 'desc',
        ])->paginate(25)->withQueryString();

        return view('forms::admin.submissions.index', compact('submissions'));
    }

    public function bulk(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:form_submissions,id'],
            'action' => ['required', 'in:mark_processed,archive'],
        ]);

        $status = $data['action'] === 'mark_processed' ? 'processed' : 'archived';
        FormSubmission::query()->whereIn('id', $data['ids'])->update(['status' => $status]);
        $auditLogger->log('forms.submissions.bulk', null, ['status' => $status, 'count' => count($data['ids'])]);

        return back()->with('status', 'Submissions updated.');
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
