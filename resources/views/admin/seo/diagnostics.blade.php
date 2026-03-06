@extends('layouts.admin')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">SEO Diagnostics</h1>

    <section class="mb-6 rounded bg-white p-4 shadow-sm">
        <h2 class="mb-2 font-medium">Missing meta_title in seo_meta</h2>
        <ul class="list-disc pl-5 text-sm text-slate-700">
            <li>Pages: {{ count($missing_meta_titles['pages']) }}</li>
            <li>Posts: {{ count($missing_meta_titles['posts']) }}</li>
            <li>Properties: {{ count($missing_meta_titles['properties']) }}</li>
        </ul>
    </section>

    <section class="mb-6 rounded bg-white p-4 shadow-sm">
        <h2 class="mb-2 font-medium">Missing hreflang translations</h2>
        <ul class="list-disc pl-5 text-sm text-slate-700">
            <li>Pages: {{ $missing_hreflang['pages']->count() }}</li>
            <li>Posts: {{ $missing_hreflang['posts']->count() }}</li>
            <li>Properties: {{ $missing_hreflang['properties']->count() }}</li>
        </ul>
    </section>

    <section class="mb-6 rounded bg-white p-4 shadow-sm">
        <h2 class="mb-2 font-medium">Redirect loops</h2>
        @if(empty($redirect_loops))
            <p class="text-sm text-emerald-700">No redirect loop detected.</p>
        @else
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($redirect_loops as $loop)
                    <li>{{ implode(' → ', $loop) }}</li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded bg-white p-4 shadow-sm">
        <h2 class="mb-2 font-medium">Sitemap cache</h2>
        <p class="text-sm text-slate-700">
            Key: <code>{{ $sitemap_cache['cache_key'] }}</code> —
            Status: {{ $sitemap_cache['is_cached'] ? 'warm' : 'cold' }}
        </p>
    </section>
@endsection
