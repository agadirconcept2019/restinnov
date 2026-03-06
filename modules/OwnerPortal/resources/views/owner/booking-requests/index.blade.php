@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Booking Requests</h2>
<table class="min-w-full rounded border bg-white text-sm"><tbody>
@foreach($bookingRequests as $br)
<tr class="border-b"><td class="p-2">{{ $br->property->translated()?->title }}</td><td class="p-2">{{ $br->status }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('owner.bookings.show',$br) }}">View</a></td></tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $bookingRequests->links() }}</div>
@endsection
