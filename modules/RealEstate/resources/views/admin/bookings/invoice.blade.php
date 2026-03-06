@extends('layouts.admin')
@section('content')
<h1>Invoice {{ $booking->invoice?->invoice_number }}</h1>
<p>Booking #{{ $booking->id }}</p>
<p>Guest: {{ $booking->guest_full_name }}</p>
<p>Property: {{ $booking->property->translated()?->title ?? $booking->property->slug }}</p>
<p>Subtotal: {{ $booking->invoice?->subtotal }}</p>
<p>Taxes: {{ $booking->invoice?->taxes_total }}</p>
<p>Total: {{ $booking->invoice?->total }}</p>
@endsection
