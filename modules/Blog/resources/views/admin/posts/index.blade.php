@extends('layouts.admin')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Blog Posts</h2>
    <a href="{{ route('admin.blog.posts.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New post</a>
</div>
<x-admin-table.filter-bar>
    <select name="status" class="rounded border p-2"><option value="">All status</option><option value="draft" @selected(request('status')==='draft')>draft</option><option value="published" @selected(request('status')==='published')>published</option></select>
    <select name="category" class="rounded border p-2"><option value="">All categories</option>@foreach($categories as $cat)<option value="{{ $cat->slug }}" @selected(request('category')===$cat->slug)>{{ $cat->translated()?->name }}</option>@endforeach</select>
    <select name="locale" class="rounded border p-2"><option value="">All locales</option>@foreach(['en','fr','es'] as $locale)<option value="{{ $locale }}" @selected(request('locale')===$locale)>{{ strtoupper($locale) }}</option>@endforeach</select>
</x-admin-table.filter-bar>
<form method="POST" action="{{ route('admin.blog.posts.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="action" class="rounded border p-2" required><option value="">Bulk action</option><option value="publish">Publish</option><option value="unpublish">Unpublish</option></select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply to selected posts?')">Apply</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
    <thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.post-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">Title</th><th>Status</th><th>Published</th><th></th></tr></thead>
    <tbody>@forelse($posts as $post) @php($tr=$post->translated()) <tr class="border-t"><td class="p-2"><input class="post-check" type="checkbox" name="ids[]" value="{{ $post->id }}"></td><td class="p-2">{{ $tr?->title }}</td><td>{{ $post->status }}</td><td>{{ $post->published_at }}</td><td><a class="text-blue-600" href="{{ route('admin.blog.posts.edit', $post) }}">Edit</a></td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-slate-500">No posts found.</td></tr>@endforelse</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
