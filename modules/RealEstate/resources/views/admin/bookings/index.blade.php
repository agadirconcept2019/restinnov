@extends('layouts.admin')
@section('content')
<h1 class="mb-4 text-2xl font-semibold">Bookings</h1>
<x-admin-table.filter-bar>
    <input name="q" value="{{ request('q') }}" class="rounded border p-2" placeholder="Search guest">
    <select name="status" class="rounded border p-2"><option value="">Status</option>@foreach(['pending','confirmed','canceled'] as $st)<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>@endforeach</select>
</x-admin-table.filter-bar>
<div class="mb-3"><form method="POST" action="{{ route('admin.exports.queue') }}">@csrf<input type="hidden" name="resource_key" value="bookings"><button class="rounded bg-emerald-700 px-3 py-2 text-white">Export CSV</button></form></div>
<form method="POST" action="{{ route('admin.real-estate.bookings.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="status" class="rounded border p-2" required><option value="">Set status</option><option value="pending">pending</option><option value="confirmed">confirmed</option><option value="canceled">canceled</option></select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply status to selected bookings?')">Apply</button>
</x-admin-table.bulk-bar>
<x-admin-table.table>
<thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.bk-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">ID</th><th class="p-2 text-left">Property</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Guest</th><th class="p-2 text-left">Total</th><th class="p-2 text-left">Invoice</th></tr></thead>
<tbody>@forelse($bookings as $booking)<tr class="border-t"><td class="p-2"><input class="bk-check" type="checkbox" name="ids[]" value="{{ $booking->id }}"></td><td class="p-2"><a href="{{ route('admin.real-estate.bookings.show',$booking) }}">#{{ $booking->id }}</a></td><td class="p-2">{{ $booking->property->translated()?->title ?? $booking->property->slug }}</td><td class="p-2">{{ $booking->status }}</td><td class="p-2">{{ $booking->guest_email }}</td><td class="p-2">{{ $booking->total }}</td><td class="p-2">{{ $booking->invoice?->invoice_number }}</td></tr>@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">No bookings found.</td></tr>@endforelse</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
