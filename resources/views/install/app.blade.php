@extends('layouts.app')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Install wizard - Step 2</h1>
<form method="post" action="{{ route('install.app.save') }}" class="grid gap-2 rounded border bg-white p-4">
    @csrf
    <input name="app_name" placeholder="App Name" required class="rounded border p-2">
    <input name="app_url" placeholder="https://example.com" required class="rounded border p-2">
    <select name="locale" class="rounded border p-2"><option>en</option><option>fr</option><option>es</option></select>
    <input name="timezone" placeholder="Europe/Paris" value="Europe/Paris" required class="rounded border p-2">
    <button class="rounded bg-blue-600 px-3 py-2 text-white">Install system</button>
</form>
@endsection
