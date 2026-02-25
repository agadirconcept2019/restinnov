@extends('layouts.public')

@if(request()->query())
    @section('robots', 'noindex,follow')
@endif

@section('content')
<h1 class="mb-4 text-2xl font-semibold">Our Properties</h1>
<form method="GET" class="mb-4 grid gap-2 rounded border bg-white p-4 md:grid-cols-4">
    <select name="city" class="rounded border p-2"><option value="">City</option>@foreach($taxonomies['cities'] as $c)<option value="{{ $c->slug }}" @selected(($filters['city'] ?? null)===$c->slug)>{{ $c->translated()?->name }}</option>@endforeach</select>
    <select name="type" class="rounded border p-2"><option value="">Type</option>@foreach($taxonomies['types'] as $t)<option value="{{ $t->slug }}" @selected(($filters['type'] ?? null)===$t->slug)>{{ $t->translated()?->name }}</option>@endforeach</select>
    <input name="checkin" type="date" value="{{ $filters['checkin'] ?? '' }}" class="rounded border p-2">
    <input name="checkout" type="date" value="{{ $filters['checkout'] ?? '' }}" class="rounded border p-2">
    <input name="guests" type="number" min="1" value="{{ $filters['guests'] ?? '' }}" class="rounded border p-2" placeholder="Guests">
    <input name="price_min" type="number" min="0" value="{{ $filters['price_min'] ?? '' }}" class="rounded border p-2" placeholder="Min price">
    <input name="price_max" type="number" min="0" value="{{ $filters['price_max'] ?? '' }}" class="rounded border p-2" placeholder="Max price">
    <select name="sort" class="rounded border p-2"><option value="newest">Newest</option><option value="price_asc" @selected(($filters['sort']??'')==='price_asc')>Price ↑</option><option value="price_desc" @selected(($filters['sort']??'')==='price_desc')>Price ↓</option><option value="featured" @selected(($filters['sort']??'')==='featured')>Featured</option></select>
    <div class="md:col-span-4 grid grid-cols-2 gap-2 md:grid-cols-6">@foreach($taxonomies['amenities'] as $a)<label class="text-sm"><input type="checkbox" name="amenities[]" value="{{ $a->slug }}" @checked(in_array($a->slug, (array)($filters['amenities'] ?? [])))> {{ $a->translated()?->name }}</label>@endforeach</div>
    <div class="md:col-span-4 flex gap-2"><button class="rounded bg-blue-600 px-4 py-2 text-white">Apply</button><a class="rounded bg-slate-200 px-4 py-2" href="{{ url()->current() }}">Reset</a></div>
</form>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
@foreach($properties as $property)
    @php($tr=$property->translated())
    <article class="rounded border bg-white p-4">
        <h2 class="font-semibold"><a href="{{ url('/properties/'.$property->slug) }}">{{ $tr?->title ?? $property->slug }}</a></h2>
        <p class="text-sm text-slate-600">{{ $property->city->translated()?->name }} · {{ number_format($property->base_price_per_night,0) }} {{ $property->currency }}/night</p>
        <p class="text-xs text-slate-500">{{ $property->type->translated()?->name }} · {{ $property->mode->translated()?->name }}</p>
    </article>
@endforeach
</div>
<div class="mt-4">{{ $properties->links() }}</div>
@endsection
