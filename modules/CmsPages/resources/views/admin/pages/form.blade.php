@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">{{ $page->exists ? 'Edit' : 'Create' }} CMS page</h2>
<form method="POST" action="{{ $page->exists ? route('admin.cms-pages.pages.update', $page) : route('admin.cms-pages.pages.store') }}" class="space-y-5">
    @csrf
    @if($page->exists) @method('PUT') @endif

    <section class="rounded border bg-white p-4">
        <h3 class="mb-2 font-semibold">1. Général</h3>
        <input name="slug" class="mb-2 w-full rounded border p-2" value="{{ old('slug', $page->slug) }}" placeholder="slug">
        <select name="template" class="mb-2 w-full rounded border p-2">@foreach(['home','services','rd','faq','contact','legal'] as $tpl)<option value="{{ $tpl }}" @selected(old('template', $page->template ?: 'home')===$tpl)>{{ $tpl }}</option>@endforeach</select>
        <select name="status" class="mb-2 w-full rounded border p-2">@foreach(['draft','published'] as $st)<option value="{{ $st }}" @selected(old('status', $page->status ?: 'draft')===$st)>{{ $st }}</option>@endforeach</select>
        <input type="datetime-local" name="published_at" class="w-full rounded border p-2" value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i')) }}">
    </section>

    @foreach(['en','fr','es'] as $locale)
    @php($tr=$page->translations->firstWhere('locale',$locale))
    <section class="rounded border bg-white p-4">
        <h3 class="mb-2 font-semibold">2/3/4. Traduction {{ strtoupper($locale) }} + template_data + SEO</h3>
        <input name="title_{{ $locale }}" class="mb-2 w-full rounded border p-2" value="{{ old('title_'.$locale, $tr?->title) }}" placeholder="Title {{ strtoupper($locale) }}">
        <textarea name="content_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="5" data-wysiwyg placeholder="Content {{ strtoupper($locale) }}">{{ old('content_'.$locale, $tr?->content) }}</textarea>
        <textarea name="template_data_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="6" placeholder='{"hero":{"title":"..."}}'>{{ old('template_data_'.$locale, $tr && $tr->template_data ? json_encode($tr->template_data, JSON_PRETTY_PRINT) : '') }}</textarea>
        <input name="meta_title_{{ $locale }}" class="mb-2 w-full rounded border p-2" value="{{ old('meta_title_'.$locale, $tr?->meta_title) }}" placeholder="Meta title">
        <textarea name="meta_description_{{ $locale }}" class="mb-2 w-full rounded border p-2" rows="2" placeholder="Meta description">{{ old('meta_description_'.$locale, $tr?->meta_description) }}</textarea>
        <input name="canonical_url_{{ $locale }}" class="w-full rounded border p-2" value="{{ old('canonical_url_'.$locale, $tr?->canonical_url) }}" placeholder="Canonical URL">
        <input name="robots_{{ $locale }}" class="mb-2 w-full rounded border p-2" value="{{ old('robots_'.$locale, 'index,follow') }}" placeholder="Robots">
        <textarea name="schema_json_{{ $locale }}" class="w-full rounded border p-2" rows="3" placeholder='{"@type":"Article"}'>{{ old('schema_json_'.$locale) }}</textarea>
    </section>
    @endforeach

    <button class="rounded bg-blue-600 px-4 py-2 text-white">Save page</button>
</form>
@endsection
