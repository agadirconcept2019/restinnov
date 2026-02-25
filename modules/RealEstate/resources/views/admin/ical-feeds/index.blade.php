@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">iCal Feeds</h2>
<form method="POST" action="{{ route('admin.real-estate.ical-feeds.store') }}" class="mb-5 grid grid-cols-1 gap-2 rounded border bg-white p-4 md:grid-cols-5">@csrf
    <select name="property_id" class="rounded border p-2" required>@foreach($properties as $p)<option value="{{ $p->id }}">{{ $p->translated()?->title ?: $p->slug }}</option>@endforeach</select>
    <input name="feed_url" class="rounded border p-2 md:col-span-2" placeholder="https://example.com/calendar.ics" required>
    <input name="sync_interval_minutes" type="number" min="5" max="1440" value="30" class="rounded border p-2">
    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button class="rounded bg-slate-900 px-3 py-2 text-white md:col-span-5">Add feed</button>
</form>

<table class="min-w-full rounded border bg-white text-sm"><thead><tr class="border-b bg-slate-50"><th class="p-2 text-left">Property</th><th class="p-2 text-left">URL</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Last sync</th><th class="p-2 text-left">Actions</th></tr></thead><tbody>
@foreach($feeds as $feed)
<tr class="border-b"><td class="p-2">{{ $feed->property->translated()?->title }}</td><td class="p-2">{{ $feed->feed_url }}</td><td class="p-2">{{ $feed->last_status ?: '-' }}</td><td class="p-2">{{ optional($feed->last_synced_at)->format('Y-m-d H:i') }}</td><td class="p-2"><form method="POST" action="{{ route('admin.real-estate.ical-feeds.sync-now', $feed) }}">@csrf<button class="text-blue-600">Sync now</button></form></td></tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $feeds->links() }}</div>
@endsection
