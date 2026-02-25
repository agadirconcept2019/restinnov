@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold">{{ $property->title }}</h1>
<p class="mb-4">{{ $property->city->name }} · {{ $property->base_price_per_night }} {{ $property->currency }}/night</p>
<form method="post" action="{{ route('properties.inquiry', $property->id) }}" class="space-y-3 rounded border bg-white p-4">
    @csrf
    <input name="name" class="w-full rounded border p-2" placeholder="Name" required>
    <input name="email" type="email" class="w-full rounded border p-2" placeholder="Email" required>
    <textarea name="message" class="w-full rounded border p-2" placeholder="Message" required></textarea>
    <button class="rounded bg-blue-600 px-4 py-2 text-white">Send inquiry</button>
</form>
@endsection
