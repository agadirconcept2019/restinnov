@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <h1 class="text-3xl font-bold">Real Estate CMS Foundation</h1>
    <p>Base Laravel 12 modulaire avec installateur web et module immobilier MVP.</p>
    <a class="rounded bg-blue-600 px-4 py-2 text-white" href="{{ route('properties.index') }}">Explore properties</a>
</div>
@endsection
