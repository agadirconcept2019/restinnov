@extends('layouts.public')

@section('meta_title', 'Blog | '.config('app.name'))
@section('meta_description', 'Insights, guides and updates from RestInnov.')
@section('canonical', url()->current())

@section('content')
<h1 class="mb-4 text-3xl font-bold">Blog</h1>
<div class="mb-4 flex flex-wrap gap-2 text-sm">
    <a class="rounded bg-slate-100 px-3 py-1" href="{{ route('blog.index') }}">All</a>
    @foreach($categories as $category)
        <a class="rounded bg-slate-100 px-3 py-1" href="{{ route('blog.category', $category->slug) }}">{{ $category->translated()?->name }}</a>
    @endforeach
</div>
<div class="space-y-4">
    @foreach($posts as $post)
        @php($tr = $post->translated())
        <article class="rounded border bg-white p-4">
            <h2 class="text-xl font-semibold"><a href="{{ route('blog.show', $post->slug) }}">{{ $tr?->title }}</a></h2>
            <p class="mt-1 text-sm text-slate-500">{{ optional($post->published_at)->format('Y-m-d') }}</p>
            <p class="mt-2">{{ $tr?->excerpt }}</p>
        </article>
    @endforeach
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
