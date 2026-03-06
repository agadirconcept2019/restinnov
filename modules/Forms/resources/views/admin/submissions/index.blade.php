@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Form submissions</h2>
<x-admin-table.filter-bar>
    <input name="q" value="{{ request('q') }}" class="rounded border p-2" placeholder="Search">
    <select name="type" class="rounded border p-2"><option value="">All types</option><option value="contact" @selected(request('type')==='contact')>contact</option><option value="quote" @selected(request('type')==='quote')>quote</option></select>
    <select name="status" class="rounded border p-2"><option value="">All status</option><option value="new" @selected(request('status')==='new')>new</option><option value="processed" @selected(request('status')==='processed')>processed</option><option value="archived" @selected(request('status')==='archived')>archived</option></select>
</x-admin-table.filter-bar>
<div class="mb-3"><form method="POST" action="{{ route('admin.exports.queue') }}">@csrf<input type="hidden" name="resource_key" value="form_submissions"><button class="rounded bg-emerald-700 px-3 py-2 text-white">Export CSV</button></form></div>
<form method="POST" action="{{ route('admin.forms.submissions.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="action" class="rounded border p-2" required><option value="">Bulk action</option><option value="mark_processed">Mark processed</option><option value="archive">Archive</option></select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply to selected submissions?')">Apply</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
    <thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.fs-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">Type</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Locale</th><th class="p-2 text-left">Created</th><th></th></tr></thead>
    <tbody>@forelse($submissions as $submission)<tr class="border-t"><td class="p-2"><input class="fs-check" type="checkbox" name="ids[]" value="{{ $submission->id }}"></td><td class="p-2">{{ $submission->form_type }}</td><td class="p-2">{{ $submission->status }}</td><td class="p-2">{{ $submission->locale }}</td><td class="p-2">{{ $submission->created_at }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('admin.forms.submissions.show', $submission) }}">View</a></td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-slate-500">No submissions found.</td></tr>@endforelse</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $submissions->links() }}</div>
@endsection
