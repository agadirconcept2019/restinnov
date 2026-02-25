@extends('layouts.app')

@section('content')
<form method="post" action="{{ route('login.attempt') }}" class="mx-auto max-w-md space-y-3 rounded border bg-white p-6">
    @csrf
    <h1 class="text-xl font-semibold">Admin login</h1>
    <input name="email" type="email" class="w-full rounded border p-2" placeholder="Email" required>
    <input name="password" type="password" class="w-full rounded border p-2" placeholder="Password" required>
    <button class="rounded bg-slate-900 px-4 py-2 text-white">Sign in</button>
</form>
@endsection
