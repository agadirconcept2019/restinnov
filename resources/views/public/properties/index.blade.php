@extends('layouts.app')

@section('content')
<h1 class="mb-4 text-2xl font-semibold">Our Properties</h1>
<div class="grid gap-4 md:grid-cols-2">
@foreach($properties as $property)
    <article class="rounded border bg-white p-4">
        <h2 class="font-semibold"><a href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a></h2>
        <p>{{ $property->city->name }} · {{ $property->base_price_per_night }} {{ $property->currency }}/night</p>
    </article>
@endforeach
</div>
<div class="mt-4">{{ $properties->links() }}</div>
@endsection
