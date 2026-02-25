@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Taxonomy: {{ $taxonomy }}</h2>
<form method="POST" action="{{ route('admin.real-estate.taxonomies.store',$taxonomy) }}" class="mb-4 grid gap-2 rounded border bg-white p-4">@csrf
    <input name="slug" class="rounded border p-2" placeholder="slug">
    <input name="name_en" class="rounded border p-2" placeholder="name en" required>
    <input name="name_fr" class="rounded border p-2" placeholder="name fr">
    @if($taxonomy==='areas')<input name="city_id" class="rounded border p-2" placeholder="city id" required>@endif
    <button class="rounded bg-blue-600 px-4 py-2 text-white">Create</button>
</form>
<table class="min-w-full rounded border bg-white text-sm"><tr class="bg-slate-100"><th class="p-2 text-left">Slug</th><th>Name</th></tr>@foreach($items as $item)<tr class="border-t"><td class="p-2">{{ $item->slug }}</td><td>{{ $item->translated()?->name }}</td></tr>@endforeach</table>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
