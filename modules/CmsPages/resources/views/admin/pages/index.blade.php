@extends('layouts.admin')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-semibold">CMS Pages</h2>
    <a href="{{ route('admin.cms-pages.pages.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New page</a>
</div>
<x-admin-table.filter-bar>
    <select name="status" class="rounded border p-2"><option value="">All status</option><option value="draft" @selected(request('status')==='draft')>draft</option><option value="published" @selected(request('status')==='published')>published</option></select>
    <select name="locale" class="rounded border p-2"><option value="">All locales</option>@foreach(['en','fr','es'] as $locale)<option value="{{ $locale }}" @selected(request('locale')===$locale)>{{ strtoupper($locale) }}</option>@endforeach</select>
</x-admin-table.filter-bar>
<form method="POST" action="{{ route('admin.cms-pages.pages.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="action" class="rounded border p-2" required><option value="">Bulk action</option><option value="publish">Publish</option><option value="unpublish">Unpublish</option></select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply to selected pages?')">Apply</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
    <thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.pg-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">Slug</th><th>Template</th><th>Status</th><th>Locales</th><th>Updated</th><th></th></tr></thead>
    <tbody>@forelse($pages as $page)<tr class="border-t"><td class="p-2"><input class="pg-check" type="checkbox" name="ids[]" value="{{ $page->id }}"></td><td class="p-2">{{ $page->slug }}</td><td>{{ $page->template }}</td><td>{{ $page->status }}</td><td>{{ $page->translations->pluck('locale')->implode(', ') }}</td><td>{{ $page->updated_at }}</td><td><a class="text-blue-600" href="{{ route('admin.cms-pages.pages.edit', $page) }}">Edit</a></td></tr>@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">No pages found.</td></tr>@endforelse</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection
