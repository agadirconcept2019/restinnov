@extends('layouts.admin')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Blog Posts</h2>
    <a href="{{ route('admin.blog.posts.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New post</a>
</div>
<form method="GET" class="mb-3 flex gap-2">
    <select name="status" class="rounded border p-2"><option value="">All status</option><option value="draft" @selected(request('status')==='draft')>draft</option><option value="published" @selected(request('status')==='published')>published</option></select>
    <select name="category" class="rounded border p-2"><option value="">All categories</option>@foreach($categories as $cat)<option value="{{ $cat->slug }}" @selected(request('category')===$cat->slug)>{{ $cat->translated()?->name }}</option>@endforeach</select>
    <button class="rounded bg-slate-800 px-4 py-2 text-white">Filter</button>
</form>
<table class="min-w-full rounded border bg-white text-sm">
    <tr class="bg-slate-100"><th class="p-2 text-left">Title</th><th>Status</th><th>Published</th><th></th></tr>
    @foreach($posts as $post)
        @php($tr=$post->translated())
        <tr class="border-t"><td class="p-2">{{ $tr?->title }}</td><td>{{ $post->status }}</td><td>{{ $post->published_at }}</td><td><a class="text-blue-600" href="{{ route('admin.blog.posts.edit', $post) }}">Edit</a></td></tr>
    @endforeach
</table>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
