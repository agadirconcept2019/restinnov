@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Owner Dashboard</h2>
<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded border bg-white p-4">Properties: {{ $propertiesCount }}</div>
    <div class="rounded border bg-white p-4">Booking requests: {{ $bookingRequestsCount }}</div>
    <div class="rounded border bg-white p-4">Inquiries: {{ $inquiriesCount }}</div>
</div>
@endsection
