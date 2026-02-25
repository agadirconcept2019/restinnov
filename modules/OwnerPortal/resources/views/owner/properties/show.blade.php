@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">{{ $property->translated()?->title }}</h2>
<p>Status: {{ $property->status }}</p>
@endsection
