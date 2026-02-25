@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Étape 4 · Installation système</h2>
    <p>Migrations, seeders core et nettoyage cache exécutés.</p>
    <p class="mt-2">storage:link : <span class="font-semibold {{ $storageLinked ? 'text-emerald-600' : 'text-amber-600' }}">{{ $storageLinked ? 'OK' : 'Échec non bloquant (mutualisé)' }}</span></p>
    <a href="{{ route('install.step5') }}" class="mt-4 inline-block rounded bg-blue-600 px-4 py-2 text-white">Continuer</a>
</div>
@endsection
