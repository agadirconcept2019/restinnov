@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Owners</h2>
<table class="min-w-full rounded border bg-white text-sm"><tbody>
@foreach($owners as $owner)
<tr class="border-b"><td class="p-2">{{ $owner->name }}</td><td class="p-2">{{ $owner->email }}</td></tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $owners->links() }}</div>
@endsection
