@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Redirects</h2>
<form method="POST" action="{{ route('admin.redirects.store') }}" class="mb-5 grid grid-cols-1 gap-2 rounded border bg-white p-4 md:grid-cols-5">
    @csrf
    <input name="from_path" class="rounded border p-2" placeholder="/old-path" required>
    <input name="to_url" class="rounded border p-2" placeholder="/new-path" required>
    <select name="status_code" class="rounded border p-2"><option>301</option><option>302</option></select>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button class="rounded bg-slate-900 px-3 py-2 text-white">Create</button>
</form>

<table class="min-w-full rounded border bg-white text-sm">
    <thead><tr class="border-b bg-slate-50"><th class="p-2 text-left">From</th><th class="p-2 text-left">To</th><th class="p-2 text-left">Code</th><th class="p-2 text-left">Hits</th><th class="p-2 text-left">Active</th></tr></thead>
    <tbody>
    @foreach($redirects as $redirect)
    <tr class="border-b"><td class="p-2">{{ $redirect->from_path }}</td><td class="p-2">{{ $redirect->to_url }}</td><td class="p-2">{{ $redirect->status_code }}</td><td class="p-2">{{ $redirect->hits }}</td><td class="p-2">{{ $redirect->is_active ? 'yes':'no' }}</td></tr>
    @endforeach
    </tbody>
</table>
<div class="mt-4">{{ $redirects->links() }}</div>
@endsection
