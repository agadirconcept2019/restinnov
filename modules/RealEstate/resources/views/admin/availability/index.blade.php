@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Availability Calendar</h2>
<form method="GET" class="mb-4 flex gap-2">
    <select name="property_id" class="rounded border p-2">@foreach($properties as $p)<option value="{{ $p->id }}" @selected($propertyId===$p->id)>{{ $p->translated()?->title ?: $p->slug }}</option>@endforeach</select>
    <input type="month" name="month" value="{{ $month }}" class="rounded border p-2">
    <button class="rounded bg-slate-900 px-3 py-2 text-white">Load</button>
</form>

<form method="POST" action="{{ route('admin.real-estate.availability.bulk-update') }}" class="mb-6 grid grid-cols-1 gap-2 rounded border bg-white p-4 md:grid-cols-6">
    @csrf
    <input type="hidden" name="property_id" value="{{ $propertyId }}">
    <input type="date" name="from_date" required class="rounded border p-2">
    <input type="date" name="to_date" required class="rounded border p-2">
    <select name="status" class="rounded border p-2"><option>available</option><option>blocked</option><option>booked</option><option>pending</option></select>
    <input type="number" name="price_per_night" class="rounded border p-2" placeholder="Price">
    <input type="number" name="minimum_stay" class="rounded border p-2" placeholder="Min stay">
    <button class="rounded bg-blue-600 px-3 py-2 text-white md:col-span-6">Apply bulk update</button>
</form>

<div class="grid grid-cols-2 gap-2 md:grid-cols-7">
@for($d=1; $d<=date('t', strtotime($month.'-01')); $d++)
    @php($date = $month.'-'.str_pad((string)$d, 2, '0', STR_PAD_LEFT))
    @php($entry = $rows[$date][0] ?? null)
    <div class="rounded border p-2 {{ !$entry || $entry->status==='available' ? 'bg-emerald-50':'bg-slate-100' }}">
        <div class="text-xs text-slate-500">{{ $date }}</div>
        <div class="text-sm">{{ $entry->status ?? 'available' }}</div>
        @if($entry && $entry->price_per_night)
            <div class="text-xs">{{ $entry->price_per_night }}</div>
        @endif
    </div>
@endfor
</div>
@endsection
