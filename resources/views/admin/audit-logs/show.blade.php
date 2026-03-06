@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Audit Log #{{ $auditLog->id }}</h2>
<div class="rounded border bg-white p-4 text-sm">
    <p><strong>Action:</strong> {{ $auditLog->action }}</p>
    <p><strong>Entity:</strong> {{ $auditLog->entity_type }}#{{ $auditLog->entity_id }}</p>
    <p><strong>Actor:</strong> {{ $auditLog->actor_user_id }}</p>
    <p><strong>Created:</strong> {{ optional($auditLog->created_at)->format('Y-m-d H:i:s') }}</p>
    <p><strong>IP Hash:</strong> {{ $auditLog->ip_hash }}</p>
    <p class="mt-3"><strong>Meta:</strong></p>
    <pre class="overflow-auto rounded bg-slate-100 p-3">{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endsection
