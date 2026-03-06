@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Submission #{{ $submission->id }}</h2>
<div class="rounded border bg-white p-4">
    <p><strong>Type:</strong> {{ $submission->form_type }}</p>
    <p><strong>Status:</strong> {{ $submission->status }}</p>
    <p><strong>Source:</strong> {{ $submission->source_url }}</p>
    <pre class="mt-3 overflow-auto rounded bg-slate-100 p-3 text-xs">{{ json_encode($submission->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    @if($submission->status !== 'processed')
        <form method="POST" action="{{ route('admin.forms.submissions.processed', $submission) }}" class="mt-3">@csrf<button class="rounded bg-blue-600 px-4 py-2 text-white">Mark processed</button></form>
    @endif
</div>
@endsection
