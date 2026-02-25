@extends('layouts.admin')
@section('content')
<h1>Email Logs</h1>
<form method="get">
<input name="status" placeholder="status" value="{{ request('status') }}">
<input name="template_key" placeholder="template_key" value="{{ request('template_key') }}">
<input name="related_type" placeholder="related_type" value="{{ request('related_type') }}">
<button>Filter</button>
</form>
<table><tr><th>ID</th><th>Template</th><th>To</th><th>Status</th><th>Attempts</th><th>Related</th><th>Action</th></tr>
@foreach($logs as $log)
<tr>
<td><a href="{{ route('admin.communications.logs.show',$log) }}">{{ $log->id }}</a></td>
<td>{{ $log->template_key }}</td>
<td>{{ $log->to_email_masked }}</td>
<td>{{ $log->status }}</td>
<td>{{ $log->attempts }}</td>
<td>{{ $log->related_type }}#{{ $log->related_id }}</td>
<td>@if($log->status==='failed' && $log->attempts < 3)<form method="post" action="{{ route('admin.communications.logs.retry',$log) }}">@csrf<button>Retry</button></form>@endif</td>
</tr>
@endforeach
</table>
{{ $logs->links() }}
@endsection
