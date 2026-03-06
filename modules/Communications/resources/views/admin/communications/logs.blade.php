@extends('layouts.admin')
@section('content')
<h1 class="mb-4 text-2xl font-semibold">Email Logs</h1>
<x-admin-table.filter-bar>
    <input name="q" value="{{ request('q') }}" class="rounded border p-2" placeholder="Search">
    <input name="status" placeholder="status" class="rounded border p-2" value="{{ request('status') }}">
    <input name="template_key" placeholder="template_key" class="rounded border p-2" value="{{ request('template_key') }}">
    <input name="related_type" placeholder="related_type" class="rounded border p-2" value="{{ request('related_type') }}">
</x-admin-table.filter-bar>
<div class="mb-3"><form method="POST" action="{{ route('admin.exports.queue') }}">@csrf<input type="hidden" name="resource_key" value="email_logs"><button class="rounded bg-emerald-700 px-3 py-2 text-white">Export CSV</button></form></div>
<form method="POST" action="{{ route('admin.communications.logs.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <input type="hidden" name="action" value="retry_failed">
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Retry selected failed logs?')">Retry failed (bulk)</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
<thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.el-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">ID</th><th class="p-2 text-left">Template</th><th class="p-2 text-left">To</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Attempts</th><th class="p-2 text-left">Related</th><th class="p-2 text-left">Action</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr class="border-t">
<td class="p-2"><input class="el-check" type="checkbox" name="ids[]" value="{{ $log->id }}"></td>
<td class="p-2"><a href="{{ route('admin.communications.logs.show',$log) }}">{{ $log->id }}</a></td>
<td class="p-2">{{ $log->template_key }}</td>
<td class="p-2">{{ $log->to_email_masked }}</td>
<td class="p-2">{{ $log->status }}</td>
<td class="p-2">{{ $log->attempts }}</td>
<td class="p-2">{{ $log->related_type }}#{{ $log->related_id }}</td>
<td class="p-2">@if($log->status==='failed' && $log->attempts < 3)<form method="post" action="{{ route('admin.communications.logs.retry',$log) }}">@csrf<button class="text-blue-600">Retry</button></form>@endif</td>
</tr>
@empty<tr><td colspan="8" class="p-6 text-center text-slate-500">No logs found.</td></tr>
@endforelse
</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
