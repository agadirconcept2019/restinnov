@extends('layouts.admin')
@section('content')
<h1>Migration Run #{{ $run->id }}</h1>
<p>Profile: {{ $run->profile?->name }}</p>
<p>Status: {{ $run->status }}</p>
<p>Summary: {{ json_encode($run->summary) }}</p>
<form method="post" action="{{ route('admin.migration.runs.retry-failed',$run) }}">@csrf<button>Retry failed items</button></form>
<form method="post" action="{{ route('admin.migration.runs.rollback',$run) }}">@csrf<button>Rollback run (created only)</button></form>
<table>
<tr><th>ID</th><th>Type</th><th>Source</th><th>Status</th><th>Result</th><th>Rollback</th><th>Error</th></tr>
@foreach($run->items as $item)
<tr>
<td>{{ $item->id }}</td><td>{{ $item->entity_type }}</td><td>{{ $item->source_id }}</td><td>{{ $item->status }}</td><td>{{ $item->result }}</td>
<td>
@if($item->result === 'created' && !$item->rolled_back_at)
<form method="post" action="{{ route('admin.migration.items.rollback',$item) }}">@csrf<button>Rollback item</button></form>
@endif
</td>
<td>{{ $item->error_excerpt }}</td>
</tr>
@endforeach
</table>
@endsection
