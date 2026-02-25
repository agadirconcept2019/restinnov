@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Media manager</h2>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="mb-4 rounded border bg-white p-4">
    @csrf
    <input type="file" name="file" required>
    <button class="rounded bg-blue-600 px-3 py-1 text-white">Upload</button>
</form>
@foreach($media as $item)
<section class="mb-3 rounded border bg-white p-3">
    <p class="text-sm font-medium">{{ $item->original_name ?? $item->filename }}</p>
    <p class="text-xs text-slate-600">{{ $item->mime_type }} · {{ $item->size }} bytes</p>
    <form method="POST" action="{{ route('admin.media.update', $item) }}" class="mt-2 grid grid-cols-3 gap-2">@csrf @method('PUT')
        <input name="alt" class="rounded border p-1" placeholder="Alt" value="{{ $item->alt }}">
        <input name="title" class="rounded border p-1" placeholder="Title" value="{{ $item->title }}">
        <input name="caption" class="rounded border p-1" placeholder="Caption" value="{{ $item->caption }}">
        <button class="rounded bg-slate-700 px-2 py-1 text-white col-span-2">Save metadata</button>
    </form>
    <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="mt-2">@csrf @method('DELETE')<button class="rounded bg-red-600 px-2 py-1 text-white">Delete</button></form>
</section>
@endforeach
@endsection
