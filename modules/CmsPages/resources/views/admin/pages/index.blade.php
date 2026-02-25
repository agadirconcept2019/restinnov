@extends('layouts.admin')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-semibold">CMS Pages</h2>
    <a href="{{ route('admin.cms-pages.pages.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New page</a>
</div>
<table class="min-w-full rounded border bg-white text-sm">
    <tr class="bg-slate-100"><th class="p-2 text-left">Slug</th><th>Template</th><th>Status</th><th>Locales</th><th>Updated</th><th></th></tr>
    @foreach($pages as $page)
        <tr class="border-t">
            <td class="p-2">{{ $page->slug }}</td>
            <td>{{ $page->template }}</td>
            <td>{{ $page->status }}</td>
            <td>{{ $page->translations->pluck('locale')->implode(', ') }}</td>
            <td>{{ $page->updated_at }}</td>
            <td><a class="text-blue-600" href="{{ route('admin.cms-pages.pages.edit', $page) }}">Edit</a></td>
        </tr>
    @endforeach
</table>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection
