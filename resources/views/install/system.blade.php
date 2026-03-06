@extends('layouts.app')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Install wizard - Step 3</h1>
<form method="post" action="{{ route('install.admin') }}" class="grid gap-2 rounded border bg-white p-4">
    @csrf
    <input name="name" placeholder="Admin name" required class="rounded border p-2">
    <input name="email" type="email" placeholder="Admin email" required class="rounded border p-2">
    <input name="password" type="password" placeholder="Password" required class="rounded border p-2">
    <input name="password_confirmation" type="password" placeholder="Confirm password" required class="rounded border p-2">
    <button class="rounded bg-blue-600 px-3 py-2 text-white">Finalize installation</button>
</form>
@endsection
