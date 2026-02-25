@extends('layouts.app')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Contact us</h1>
<form method="post" action="{{ route('contact.submit') }}" class="space-y-3 rounded border bg-white p-4">
    @csrf
    <input name="name" class="w-full rounded border p-2" placeholder="Name" required>
    <input name="email" type="email" class="w-full rounded border p-2" placeholder="Email" required>
    <textarea name="message" class="w-full rounded border p-2" placeholder="Message" required></textarea>
    <input name="website" class="hidden" tabindex="-1" autocomplete="off">
    <button class="rounded bg-blue-600 px-4 py-2 text-white">Send message</button>
</form>
@endsection
