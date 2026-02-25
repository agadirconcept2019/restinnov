@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Booking Request #{{ $bookingRequest->id }}</h2>
<div class="rounded border bg-white p-4">
<p><strong>Property:</strong> {{ $bookingRequest->property->translated()?->title }}</p>
<p><strong>Customer:</strong> {{ $bookingRequest->full_name }} ({{ $bookingRequest->email }})</p>
<p><strong>Dates:</strong> {{ $bookingRequest->checkin_date?->format('Y-m-d') }} → {{ $bookingRequest->checkout_date?->format('Y-m-d') }}</p>
<p><strong>Estimated total:</strong> {{ $bookingRequest->estimated_total }}</p>
<p><strong>Status:</strong> {{ $bookingRequest->status }}</p>
<form method="POST" action="{{ route('admin.real-estate.booking-requests.status', $bookingRequest) }}" class="mt-3 flex gap-2">@csrf
    <select name="status" class="rounded border p-2">@foreach(['pending','confirmed','rejected','canceled','expired'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white">Update status</button>
</form>
</div>
@endsection
