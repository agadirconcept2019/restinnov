@extends('layouts.public')
@section('content')
<h1>Booking summary</h1>
<p>Property: {{ $booking->property->translated()?->title ?? $booking->property->slug }}</p>
<p>Status: {{ $booking->status }}</p>
<p>Dates: {{ $booking->checkin_date->toDateString() }} → {{ $booking->checkout_date->toDateString() }}</p>
<p>Total: {{ $booking->total }}</p>
@if($booking->invoice)
<p><a href="{{ URL::temporarySignedRoute('realestate.client.invoice.download', now()->addDays(7), ['invoice' => $booking->invoice->id]) }}">Download invoice</a></p>
@endif
@endsection
