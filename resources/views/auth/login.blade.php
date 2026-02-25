@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-md rounded-lg border bg-white p-6">
    <h2 class="mb-4 text-xl font-semibold">Connexion admin</h2>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-3">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded border p-2" placeholder="Email">
        <input type="password" name="password" required class="w-full rounded border p-2" placeholder="Mot de passe">
        <button class="w-full rounded bg-slate-900 py-2 text-white">Se connecter</button>
    </form>
</div>
@endsection
