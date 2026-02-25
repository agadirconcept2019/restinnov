@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">{{ $post->exists ? 'Edit' : 'Create' }} Post</h2>
<form method="POST" action="{{ $post->exists ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}" class="space-y-5">
    @csrf
    @if($post->exists) @method('PUT') @endif

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">General & Publication</h3>
        <input name="slug" class="mb-2 w-full rounded border p-2" value="{{ old('slug', $post->slug) }}" placeholder="slug" required>
        <select name="status" class="mb-2 w-full rounded border p-2"><option value="draft" @selected(old('status',$post->status?:'draft')==='draft')>draft</option><option value="published" @selected(old('status',$post->status)==='published')>published</option></select>
        <input name="published_at" type="datetime-local" class="mb-2 w-full rounded border p-2" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\\TH:i')) }}">
        <label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$post->is_featured))> Featured</label>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">Categories</h3>
        <div class="grid grid-cols-3 gap-2">@foreach($categories as $cat)<label><input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" @checked(in_array($cat->id, old('category_ids', $post->categories->pluck('id')->all() ?? [])))> {{ $cat->translated()?->name }}</label>@endforeach</div>
    </section>

    @foreach(['en','fr','es'] as $locale)
    @php($tr = $post->translations->firstWhere('locale',$locale))
    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">Content + SEO {{ strtoupper($locale) }}</h3>
        <input name="title_{{ $locale }}" class="mb-2 w-full rounded border p-2" value="{{ old('title_'.$locale, $tr?->title) }}" placeholder="Title {{ strtoupper($locale) }}">
        <textarea name="excerpt_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="2" placeholder="Excerpt">{{ old('excerpt_'.$locale, $tr?->excerpt) }}</textarea>
        <textarea name="content_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="8" placeholder="Content">{{ old('content_'.$locale, $tr?->content) }}</textarea>
        <input name="meta_title_{{ $locale }}" class="mb-2 w-full rounded border p-2" value="{{ old('meta_title_'.$locale, $tr?->meta_title) }}" placeholder="Meta title">
        <textarea name="meta_description_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="2" placeholder="Meta description">{{ old('meta_description_'.$locale, $tr?->meta_description) }}</textarea>
        <input name="canonical_url_{{ $locale }}" class="w-full rounded border p-2" value="{{ old('canonical_url_'.$locale, $tr?->canonical_url) }}" placeholder="Canonical URL">
    </section>
    @endforeach

    <div class="flex gap-3">
        <button class="rounded bg-blue-600 px-4 py-2 text-white">Save</button>
        @if($post->exists)
            <button form="delete-post" class="rounded bg-red-600 px-4 py-2 text-white" type="submit">Delete</button>
        @endif
    </div>
</form>
@if($post->exists)
<form id="delete-post" method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}">@csrf @method('DELETE')</form>
@endif
@endsection
