@extends('layouts.admin')
@section('content')
<h1>Migration run #{{ $run->id }}</h1>
<p>Status: {{ $run->status }}</p>
<p>Summary: {{ json_encode($run->summary) }}</p>
<form method="post" action="{{ route('admin.migration.retry-failed',$run) }}">@csrf<button>Retry failed items</button></form>
<table>
<tr><th>Type</th><th>Source</th><th>Status</th><th>Error</th></tr>
@foreach($run->items as $item)
<tr><td>{{ $item->entity_type }}</td><td>{{ $item->source_id }}</td><td>{{ $item->status }}</td><td>{{ $item->error_excerpt }}</td></tr>
@endforeach
</table>
@endsection
