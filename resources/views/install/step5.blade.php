@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Étape 5 · Création administrateur</h2>
    <form method="POST" action="{{ route('install.step5.store') }}" class="grid gap-3">
        @csrf
        <input name="name" value="{{ old('name') }}" class="rounded border p-2" placeholder="Nom" required>
        <input name="email" type="email" value="{{ old('email') }}" class="rounded border p-2" placeholder="Email" required>
        <input name="password" type="password" class="rounded border p-2" placeholder="Mot de passe fort" required>
        <input name="password_confirmation" type="password" class="rounded border p-2" placeholder="Confirmer le mot de passe" required>
        <button class="rounded bg-blue-600 py-2 text-white">Créer l'admin et finaliser</button>
    </form>
</div>
@endsection
