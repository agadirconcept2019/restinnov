@extends('layouts.admin')
@section('content')
<h1>Booking #{{ $booking->id }}</h1>
<p>Status: {{ $booking->status }}</p>
<p>Guest: {{ $booking->guest_full_name }} ({{ $booking->guest_email }})</p>
<p>Dates: {{ $booking->checkin_date->toDateString() }} → {{ $booking->checkout_date->toDateString() }}</p>
<p>Total: {{ $booking->total }}</p>
<p><a href="{{ route('admin.real-estate.bookings.invoice',$booking) }}">Invoice {{ $booking->invoice?->invoice_number }}</a></p>
<p><a href="{{ URL::temporarySignedRoute('realestate.client.booking.summary', now()->addDays(7), ['booking' => $booking->id]) }}">Client booking summary link (7d)</a></p>
@if($booking->invoice)
<p><a href="{{ URL::temporarySignedRoute('realestate.client.invoice.download', now()->addDays(7), ['invoice' => $booking->invoice->id]) }}">Client invoice link (7d)</a></p>
@endif
@if($booking->status !== 'canceled')
<form method="post" action="{{ route('admin.real-estate.bookings.cancel',$booking) }}">@csrf<button>Cancel booking</button></form>
@endif
<form method="post" action="{{ route('admin.real-estate.bookings.resend',$booking) }}">@csrf<button>Resend confirmation</button></form>

<h2>Communications timeline</h2>
<table>
<tr><th>ID</th><th>Template</th><th>Status</th><th>Attempts</th><th>Recipient</th><th>When</th></tr>
@forelse($communicationLogs as $log)
<tr><td>{{ $log->id }}</td><td>{{ $log->template_key }}</td><td>{{ $log->status }}</td><td>{{ $log->attempts }}</td><td>{{ $log->to_email_masked }}</td><td>{{ $log->created_at }}</td></tr>
@empty
<tr><td colspan="6">No communication logs yet.</td></tr>
@endforelse
</table>
@endsection
