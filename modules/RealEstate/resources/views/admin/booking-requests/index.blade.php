@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Booking Requests</h2>
<x-admin-table.filter-bar>
    <input name="q" value="{{ request('q') }}" class="rounded border p-2" placeholder="Search guest">
    <select name="status" class="rounded border p-2"><option value="">All status</option>@foreach(['new','pending','confirmed','rejected','canceled','expired'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select>
</x-admin-table.filter-bar>
<form method="POST" action="{{ route('admin.real-estate.booking-requests.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="status" class="rounded border p-2" required><option value="">Bulk status</option><option value="pending">pending</option><option value="rejected">rejected</option><option value="canceled">canceled</option><option value="expired">expired</option></select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply status to selected requests?')">Apply</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
<thead><tr class="border-b bg-slate-50"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.br-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">Property</th><th class="p-2 text-left">Dates</th><th class="p-2 text-left">Guests</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Action</th></tr></thead>
<tbody>@forelse($bookingRequests as $br)<tr class="border-b"><td class="p-2"><input class="br-check" type="checkbox" name="ids[]" value="{{ $br->id }}"></td><td class="p-2">{{ $br->property->translated()?->title }}</td><td class="p-2">{{ $br->checkin_date?->format('Y-m-d') }} → {{ $br->checkout_date?->format('Y-m-d') }}</td><td class="p-2">{{ $br->guests }}</td><td class="p-2">{{ $br->status }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('admin.real-estate.booking-requests.show',$br) }}">View</a></td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-slate-500">No booking requests found.</td></tr>@endforelse</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $bookingRequests->links() }}</div>
@endsection
