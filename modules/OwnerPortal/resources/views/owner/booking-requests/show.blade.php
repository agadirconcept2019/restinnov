@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Booking #{{ $bookingRequest->id }}</h2>
<p>{{ $bookingRequest->full_name }} - {{ $bookingRequest->email }}</p>
<p>{{ $bookingRequest->checkin_date?->format('Y-m-d') }} → {{ $bookingRequest->checkout_date?->format('Y-m-d') }}</p>
<form method="POST" action="{{ route('owner.bookings.status',$bookingRequest) }}" class="mt-3">@csrf<select name="status" class="rounded border p-2">@foreach(['pending','confirmed','rejected','canceled'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select><button class="ml-2 rounded bg-blue-600 px-3 py-2 text-white">Update</button></form>
<form method="POST" action="{{ route('owner.bookings.notes.store',$bookingRequest) }}" class="mt-3">@csrf<textarea name="note" class="w-full rounded border p-2" required></textarea><button class="mt-2 rounded bg-slate-900 px-3 py-2 text-white">Add note</button></form>
<ul class="mt-4 list-disc pl-5">@foreach($notes as $note)<li>{{ $note->note }} ({{ $note->visibility }})</li>@endforeach</ul>
@endsection
