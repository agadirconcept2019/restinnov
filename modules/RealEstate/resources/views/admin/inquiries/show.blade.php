@extends('layouts.admin')
@section('content')
<h2 class="mb-3 text-2xl font-semibold">Inquiry detail</h2>
<div class="rounded border bg-white p-4">
    <p><b>Name:</b> {{ $inquiry->first_name }} {{ $inquiry->last_name }}</p>
    <p><b>Email:</b> {{ $inquiry->email }}</p>
    <p><b>Status:</b> {{ $inquiry->status }}</p>
    <p><b>Message:</b> {{ $inquiry->message }}</p>
    <p><b>Source:</b> {{ $inquiry->source_page }}</p>
</div>
@endsection
