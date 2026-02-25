@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Audit Logs</h2>
<form class="mb-4 grid grid-cols-1 gap-2 md:grid-cols-4">
    <input name="action" value="{{ request('action') }}" class="rounded border p-2" placeholder="Action">
    <input name="entity_type" value="{{ request('entity_type') }}" class="rounded border p-2" placeholder="Entity class">
    <input name="date" type="date" value="{{ request('date') }}" class="rounded border p-2">
    <button class="rounded bg-slate-900 px-3 py-2 text-white">Filter</button>
</form>
<table class="min-w-full rounded border bg-white text-sm">
    <thead><tr class="border-b bg-slate-50"><th class="p-2 text-left">At</th><th class="p-2 text-left">Action</th><th class="p-2 text-left">Entity</th><th class="p-2 text-left">Actor</th><th class="p-2 text-left"></th></tr></thead>
    <tbody>
    @foreach($logs as $log)
        <tr class="border-b"><td class="p-2">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td><td class="p-2">{{ $log->action }}</td><td class="p-2">{{ class_basename((string)$log->entity_type) }}#{{ $log->entity_id }}</td><td class="p-2">{{ $log->actor_user_id }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('admin.audit-logs.show', $log) }}">View</a></td></tr>
    @endforeach
    </tbody>
</table>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
