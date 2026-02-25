@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Menus manager</h2>
@foreach($menus as $menu)
<section class="mb-4 rounded border bg-white p-4">
    <h3 class="font-semibold">{{ $menu->name }} ({{ $menu->slug }})</h3>
    <form method="POST" action="{{ route('admin.menus.reorder', $menu) }}" class="mt-2 space-y-2">
        @csrf
        @foreach($menu->items as $item)
            <div class="grid grid-cols-12 gap-2 text-sm">
                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                <input name="items[{{ $loop->index }}][sort_order]" value="{{ $item->sort_order }}" class="col-span-2 rounded border p-1">
                <select name="items[{{ $loop->index }}][parent_id]" class="col-span-3 rounded border p-1"><option value="">root</option>@foreach($menu->items as $candidate)<option value="{{ $candidate->id }}" @selected($item->parent_id===$candidate->id) @disabled($item->id===$candidate->id)>{{ $candidate->label }}</option>@endforeach</select>
                <input value="{{ $item->locale ?: 'all' }}" disabled class="col-span-2 rounded border p-1 bg-slate-100">
                <input value="{{ $item->label }}" disabled class="col-span-5 rounded border p-1 bg-slate-100">
            </div>
        @endforeach
        <button class="rounded bg-blue-600 px-3 py-1 text-white">Save order</button>
    </form>
</section>
@endforeach
@endsection
