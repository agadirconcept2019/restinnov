@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Export runs</h2>
<x-admin-table.table>
    <thead><tr class="bg-slate-100"><th class="p-2 text-left">ID</th><th class="p-2 text-left">Resource</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Rows</th><th class="p-2 text-left">Created</th><th class="p-2 text-left">Action</th></tr></thead>
    <tbody>
    @forelse($runs as $run)
        <tr class="border-t"><td class="p-2">{{ $run->id }}</td><td class="p-2">{{ $run->resource_key }}</td><td class="p-2">{{ $run->status }}</td><td class="p-2">{{ $run->rows_count }}</td><td class="p-2">{{ $run->created_at }}</td><td class="p-2">@if($run->status==='completed')<a class="text-blue-600" href="{{ route('admin.exports.download', $run) }}">Download</a>@endif</td></tr>
    @empty
        <tr><td class="p-4 text-slate-500" colspan="6">No exports yet.</td></tr>
    @endforelse
    </tbody>
</x-admin-table.table>
<div class="mt-4">{{ $runs->links() }}</div>
@endsection
