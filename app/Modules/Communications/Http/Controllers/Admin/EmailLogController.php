<?php

namespace App\Modules\Communications\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\EmailDispatcher;

class EmailLogController extends Controller
{
    public function index()
    {
        $logs = EmailLog::query()
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->when(request('template_key'), fn ($q, $v) => $q->where('template_key', $v))
            ->when(request('related_type'), fn ($q, $v) => $q->where('related_type', $v))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('communications::admin.communications.logs', compact('logs'));
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
