@extends('layouts.admin')
@section('content')
<h1>Bookings</h1>
<table>
<tr><th>ID</th><th>Property</th><th>Status</th><th>Guest</th><th>Total</th><th>Invoice</th></tr>
@foreach($bookings as $booking)
<tr>
<td><a href="{{ route('admin.real-estate.bookings.show',$booking) }}">#{{ $booking->id }}</a></td>
<td>{{ $booking->property->translated()?->title ?? $booking->property->slug }}</td>
<td>{{ $booking->status }}</td>
<td>{{ $booking->guest_email }}</td>
<td>{{ $booking->total }}</td>
<td>{{ $booking->invoice?->invoice_number }}</td>
</tr>
@endforeach
</table>
{{ $bookings->links() }}
@endsection
