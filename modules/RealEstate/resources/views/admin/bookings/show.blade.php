@extends('layouts.admin')
@section('content')
<h1>Booking #{{ $booking->id }}</h1>
<p>Status: {{ $booking->status }}</p>
<p>Guest: {{ $booking->guest_full_name }} ({{ $booking->guest_email }})</p>
<p>Dates: {{ $booking->checkin_date->toDateString() }} → {{ $booking->checkout_date->toDateString() }}</p>
<p>Total: {{ $booking->total }}</p>
<p><a href="{{ route('admin.real-estate.bookings.invoice',$booking) }}">Invoice {{ $booking->invoice?->invoice_number }}</a></p>
@if($booking->status !== 'canceled')
<form method="post" action="{{ route('admin.real-estate.bookings.cancel',$booking) }}">@csrf<button>Cancel booking</button></form>
@endif
@endsection
