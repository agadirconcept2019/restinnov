@extends('layouts.admin')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Properties</h2>
    <div class="flex gap-2">
        <a class="rounded bg-slate-700 px-3 py-2 text-white" href="{{ route('admin.exports.index') }}">Exports</a>
        <a class="rounded bg-blue-600 px-4 py-2 text-white" href="{{ route('admin.real-estate.properties.create') }}">Add property</a>
    </div>
</div>

<x-admin-table.filter-bar>
    <input name="q" value="{{ request('q') }}" class="rounded border p-2" placeholder="Search slug">
    <select name="status" class="rounded border p-2"><option value="">Status</option>@foreach(['draft','published'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select>
    <select name="city_id" class="rounded border p-2"><option value="">City</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected((string)request('city_id')===(string)$city->id)>{{ $city->translated()?->name }}</option>@endforeach</select>
    <select name="property_type_id" class="rounded border p-2"><option value="">Type</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string)request('property_type_id')===(string)$type->id)>{{ $type->translated()?->name }}</option>@endforeach</select>
    <select name="owner_user_id" class="rounded border p-2"><option value="">Owner</option>@foreach($owners as $owner)<option value="{{ $owner->id }}" @selected((string)request('owner_user_id')===(string)$owner->id)>{{ $owner->name }}</option>@endforeach</select>
    <select name="featured" class="rounded border p-2"><option value="">Featured</option><option value="1" @selected(request('featured')==='1')>Yes</option><option value="0" @selected(request('featured')==='0')>No</option></select>
</x-admin-table.filter-bar>

<div class="mb-3 flex flex-wrap gap-2">
    <form method="POST" action="{{ route('admin.saved-views.store') }}" class="flex gap-2">@csrf
        <input type="hidden" name="resource_key" value="properties">
        <input name="name" class="rounded border p-2" placeholder="Save current view as">
        <label class="text-sm"><input type="checkbox" name="is_default" value="1"> default</label>
        <button class="rounded bg-slate-700 px-3 py-2 text-white">Save view</button>
    </form>
    @foreach($savedViews as $view)
        <form method="POST" action="{{ route('admin.saved-views.apply', $view) }}">@csrf<button class="rounded border px-2 py-1 text-sm">{{ $view->name }}</button></form>
    @endforeach
    <form method="POST" action="{{ route('admin.exports.queue') }}">@csrf<input type="hidden" name="resource_key" value="properties"><button class="rounded bg-emerald-700 px-3 py-2 text-white">Export CSV</button></form>
</div>

<form method="POST" action="{{ route('admin.real-estate.properties.bulk') }}">@csrf
<x-admin-table.bulk-bar>
    <select name="action" class="rounded border p-2" required>
        <option value="">Bulk action</option>
        <option value="publish">Publish</option>
        <option value="archive">Unpublish</option>
        <option value="assign_owner">Assign owner</option>
    </select>
    <select name="owner_user_id" class="rounded border p-2"><option value="">Owner (for assign)</option>@foreach($owners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select>
    <button class="rounded bg-blue-600 px-3 py-2 text-white" onclick="return confirm('Apply bulk action?')">Apply</button>
</x-admin-table.bulk-bar>

<x-admin-table.table>
<thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.prop-check').forEach(c=>c.checked=this.checked)"></th><th class="p-2 text-left">Title</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">City</th><th class="p-2 text-left">Owner</th><th class="p-2 text-left">Featured</th><th class="p-2 text-left"></th></tr></thead>
<tbody>
@forelse($properties as $property)
@php($tr=$property->translated())
<tr class="border-t"><td class="p-2"><input class="prop-check" type="checkbox" name="ids[]" value="{{ $property->id }}"></td><td class="p-2">{{ $tr?->title }}</td><td class="p-2">{{ $property->status }}</td><td class="p-2">{{ $property->city->translated()?->name }}</td><td class="p-2">{{ $property->owner?->name }}</td><td class="p-2">{{ $property->is_featured ? 'yes' : 'no' }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('admin.real-estate.properties.edit',$property) }}">Edit</a></td></tr>
@empty
<tr><td colspan="7" class="p-6 text-center text-slate-500">No properties found for this view.</td></tr>
@endforelse
</tbody>
</x-admin-table.table>
</form>
<div class="mt-4">{{ $properties->links() }}</div>
@endsection
