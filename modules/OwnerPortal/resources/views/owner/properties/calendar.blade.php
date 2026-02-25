@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Calendar - {{ $property->translated()?->title }}</h2>
<form class="mb-4"><input type="month" name="month" value="{{ $month }}" class="rounded border p-2"><button class="rounded bg-slate-900 px-3 py-2 text-white">Load</button></form>
<form method="POST" action="{{ route('owner.properties.calendar.bulk-update', $property) }}" class="mb-4 grid grid-cols-1 gap-2 rounded border bg-white p-4 md:grid-cols-5">@csrf
    <input type="hidden" name="property_id" value="{{ $property->id }}">
    <input type="date" name="from_date" required class="rounded border p-2">
    <input type="date" name="to_date" required class="rounded border p-2">
    <select name="status" class="rounded border p-2"><option>available</option><option>blocked</option></select>
    <input type="number" name="price_per_night" class="rounded border p-2" placeholder="Price">
    <button class="rounded bg-blue-600 px-3 py-2 text-white md:col-span-5">Apply</button>
</form>
<div class="grid grid-cols-2 gap-2 md:grid-cols-7">@foreach($rows as $row)<div class="rounded border p-2"><div class="text-xs">{{ $row->date }}</div><div>{{ $row->status }}</div></div>@endforeach</div>
@endsection
