@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Permissions</h2>
<form method="GET" class="mb-4">
    <input name="q" value="{{ $search }}" class="rounded border p-2" placeholder="Search permission">
    <button class="rounded bg-blue-600 px-3 py-2 text-white">Search</button>
</form>
<div class="rounded border bg-white p-4">
    <ul class="space-y-1 text-sm">
        @foreach($permissions as $permission)
            <li>{{ $permission->slug }}</li>
        @endforeach
    </ul>
</div>
@endsection
