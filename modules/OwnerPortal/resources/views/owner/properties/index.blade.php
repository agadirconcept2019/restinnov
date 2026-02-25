@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">My Properties</h2>
<table class="min-w-full rounded border bg-white text-sm"><tbody>
@foreach($properties as $property)
<tr class="border-b"><td class="p-2">{{ $property->translated()?->title }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('owner.properties.show',$property) }}">View</a></td><td class="p-2"><a class="text-blue-600" href="{{ route('owner.properties.calendar',$property) }}">Calendar</a></td></tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $properties->links() }}</div>
@endsection
