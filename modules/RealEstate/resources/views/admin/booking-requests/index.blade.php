@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Booking Requests</h2>
<form class="mb-4"><select name="status" class="rounded border p-2"><option value="">All status</option>@foreach(['new','pending','confirmed','rejected','canceled','expired'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select><button class="ml-2 rounded bg-slate-900 px-3 py-2 text-white">Filter</button></form>
<table class="min-w-full rounded border bg-white text-sm">
<thead><tr class="border-b bg-slate-50"><th class="p-2 text-left">Property</th><th class="p-2 text-left">Dates</th><th class="p-2 text-left">Guests</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Action</th></tr></thead>
<tbody>@foreach($bookingRequests as $br)<tr class="border-b"><td class="p-2">{{ $br->property->translated()?->title }}</td><td class="p-2">{{ $br->checkin_date?->format('Y-m-d') }} → {{ $br->checkout_date?->format('Y-m-d') }}</td><td class="p-2">{{ $br->guests }}</td><td class="p-2">{{ $br->status }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('admin.real-estate.booking-requests.show',$br) }}">View</a></td></tr>@endforeach</tbody>
</table>
<div class="mt-4">{{ $bookingRequests->links() }}</div>
@endsection
