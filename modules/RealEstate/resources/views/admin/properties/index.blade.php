@extends('layouts.admin')
@section('content')
<div class="mb-4 flex items-center justify-between"><h2 class="text-2xl font-semibold">Properties</h2><a class="rounded bg-blue-600 px-4 py-2 text-white" href="{{ route('admin.real-estate.properties.create') }}">Add property</a></div>
<table class="min-w-full overflow-hidden rounded border bg-white text-sm">
<tr class="bg-slate-100"><th class="p-2 text-left">Title</th><th>Status</th><th>City</th><th></th></tr>
@foreach($properties as $property)
@php($tr=$property->translated())
<tr class="border-t"><td class="p-2">{{ $tr?->title }}</td><td>{{ $property->status }}</td><td>{{ $property->city->translated()?->name }}</td><td><a class="text-blue-600" href="{{ route('admin.real-estate.properties.edit',$property) }}">Edit</a></td></tr>
@endforeach
</table>
<div class="mt-4">{{ $properties->links() }}</div>
@endsection
