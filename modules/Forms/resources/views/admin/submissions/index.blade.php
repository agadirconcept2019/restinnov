@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Form submissions</h2>
<form method="GET" class="mb-3 flex gap-2">
    <select name="type" class="rounded border p-2"><option value="">All types</option><option value="contact" @selected(request('type')==='contact')>contact</option><option value="quote" @selected(request('type')==='quote')>quote</option></select>
    <select name="status" class="rounded border p-2"><option value="">All status</option><option value="new" @selected(request('status')==='new')>new</option><option value="processed" @selected(request('status')==='processed')>processed</option><option value="archived" @selected(request('status')==='archived')>archived</option></select>
    <button class="rounded bg-slate-800 px-4 py-2 text-white">Filter</button>
</form>
<table class="min-w-full rounded border bg-white text-sm">
    <tr class="bg-slate-100"><th class="p-2 text-left">Type</th><th>Status</th><th>Locale</th><th>Created</th><th></th></tr>
    @foreach($submissions as $submission)
    <tr class="border-t"><td class="p-2">{{ $submission->form_type }}</td><td>{{ $submission->status }}</td><td>{{ $submission->locale }}</td><td>{{ $submission->created_at }}</td><td><a class="text-blue-600" href="{{ route('admin.forms.submissions.show', $submission) }}">View</a></td></tr>
    @endforeach
</table>
<div class="mt-4">{{ $submissions->links() }}</div>
@endsection
