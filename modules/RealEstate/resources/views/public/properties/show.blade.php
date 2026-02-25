@extends('layouts.public')

@section('content')
@php($tr=$property->translated())
<h1 class="text-3xl font-bold">{{ $tr?->title }}</h1>
<p class="mb-3 text-slate-600">{{ $property->city->translated()?->name }} @if($property->area) · {{ $property->area->translated()?->name }} @endif</p>
<p class="mb-4 font-semibold">{{ number_format($property->base_price_per_night,0) }} {{ $property->currency }} / nuit</p>
<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-4">
        <div class="rounded border bg-white p-4">
            <h2 class="font-semibold">Description</h2>
            <p>{{ $tr?->description }}</p>
        </div>
        <div class="rounded border bg-white p-4">
            <h2 class="font-semibold">Détails</h2>
            <p>Guests: {{ $property->max_guests }} · Bedrooms: {{ $property->bedrooms }} · Beds: {{ $property->beds }} · Bathrooms: {{ $property->bathrooms }}</p>
            <p>Check-in: {{ $property->checkin_from }} · Check-out: {{ $property->checkout_until }}</p>
        </div>
        <div class="rounded border bg-white p-4">
            <h2 class="font-semibold">Amenities</h2>
            <ul class="list-disc pl-5">@foreach($property->amenities as $amenity)<li>{{ $amenity->translated()?->name }}</li>@endforeach</ul>
        </div>
        <div class="rounded border bg-white p-4">
            <h2 class="font-semibold">Disponibilités (90 jours)</h2>
            <div class="grid grid-cols-3 gap-1 text-xs">@foreach($availability as $day)<span class="rounded px-2 py-1 {{ $day->status==='available'?'bg-emerald-100':'bg-slate-200' }}">{{ $day->date }} · {{ $day->status }}</span>@endforeach</div>
        </div>
    </div>
    <div class="rounded border bg-white p-4">
        <h2 class="mb-3 font-semibold">Inquiry</h2>
        <form method="POST" action="{{ route('realestate.inquiry.store', $property) }}" class="space-y-2">
            @csrf
            <input name="first_name" required class="w-full rounded border p-2" placeholder="First name">
            <input name="last_name" required class="w-full rounded border p-2" placeholder="Last name">
            <input name="email" type="email" required class="w-full rounded border p-2" placeholder="Email">
            <input name="phone" class="w-full rounded border p-2" placeholder="Phone">
            <input name="checkin" type="date" class="w-full rounded border p-2">
            <input name="checkout" type="date" class="w-full rounded border p-2">
            <input name="guests" type="number" min="1" class="w-full rounded border p-2" placeholder="Guests">
            <textarea name="message" class="w-full rounded border p-2" placeholder="Message"></textarea>
            <button class="rounded bg-blue-600 px-4 py-2 text-white">Send inquiry</button>
        </form>
    </div>
</div>
@endsection
