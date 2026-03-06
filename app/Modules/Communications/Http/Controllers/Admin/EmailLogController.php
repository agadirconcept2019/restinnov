<?php

namespace App\Modules\Communications\Http\Controllers\Admin;

use App\Core\AdminTable\AdminTableQuery;
use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\EmailDispatcher;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(AdminTableQuery $adminTableQuery)
    {
        $query = EmailLog::query();
        $logs = $adminTableQuery->apply($query, [
            'search' => ['template_key', 'to_email_masked', 'subject'],
            'filters' => [
                'status' => fn ($q, $v) => $q->where('status', $v),
                'template_key' => fn ($q, $v) => $q->where('template_key', $v),
                'related_type' => fn ($q, $v) => $q->where('related_type', $v),
            ],
            'sorts' => ['id', 'created_at', 'status', 'attempts'],
            'default_sort' => 'created_at',
            'default_dir' => 'desc',
        ])->paginate(30)->withQueryString();

        return view('communications::admin.communications.logs', compact('logs'));
    }

    public function bulk(Request $request, EmailDispatcher $dispatcher, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:email_logs,id'],
            'action' => ['required', 'in:retry_failed'],
        ]);

        $retried = 0;
        foreach (EmailLog::query()->whereIn('id', $data['ids'])->get() as $log) {
            $retried += $dispatcher->retry($log) ? 1 : 0;
        }

        $auditLogger->log('communications.logs.bulk_retry', null, ['count' => $retried]);

        return back()->with('status', "{$retried} log(s) retried.");
    }

    public function show(EmailLog $log)
    {
        return view('communications::admin.communications.log-show', compact('log'));
    }

    public function retry(EmailLog $log, EmailDispatcher $dispatcher)
    {
        $ok = $dispatcher->retry($log);

        return back()->with('status', $ok ? 'Retry queued.' : 'Retry refused (attempt limit or invalid recipient).');
    }
}
