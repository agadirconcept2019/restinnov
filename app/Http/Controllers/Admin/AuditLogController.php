<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::query()
            ->when(request('action'), fn ($q, $v) => $q->where('action', 'like', "%$v%"))
            ->when(request('entity_type'), fn ($q, $v) => $q->where('entity_type', $v))
            ->when(request('date'), fn ($q, $v) => $q->whereDate('created_at', $v))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
