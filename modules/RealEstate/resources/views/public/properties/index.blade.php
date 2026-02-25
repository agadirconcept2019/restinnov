@extends('layouts.public')

@section('content')
<h1 class="mb-4 text-2xl font-semibold">Our Properties</h1>
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
