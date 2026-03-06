@extends('layouts.public')

@php($tr = $post->translated())
@section('meta_title', $tr?->meta_title ?: $tr?->title)
@section('meta_description', $tr?->meta_description ?: ($tr?->excerpt ?? ''))
@section('canonical', $tr?->canonical_url ?: url()->current())

@section('content')
<article class="rounded border bg-white p-6">
    <h1 class="text-3xl font-bold">{{ $tr?->title }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ optional($post->published_at)->format('Y-m-d') }}</p>
    <div class="prose mt-4 max-w-none">{!! nl2br(e($tr?->content)) !!}</div>
</article>
@endsection
