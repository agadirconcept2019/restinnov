@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Étape 2 · Base de données</h2>
    <form method="POST" action="{{ route('install.step2.store') }}" class="grid gap-3">
        @csrf
        <input name="db_host" value="{{ old('db_host', data_get($state, 'payload.db.db_host', '127.0.0.1')) }}" class="rounded border p-2" placeholder="DB_HOST" required>
        <input name="db_port" value="{{ old('db_port', data_get($state, 'payload.db.db_port', '3306')) }}" class="rounded border p-2" placeholder="DB_PORT" required>
        <input name="db_database" value="{{ old('db_database', data_get($state, 'payload.db.db_database')) }}" class="rounded border p-2" placeholder="DB_DATABASE" required>
        <input name="db_username" value="{{ old('db_username', data_get($state, 'payload.db.db_username')) }}" class="rounded border p-2" placeholder="DB_USERNAME" required>
        <input name="db_password" type="password" class="rounded border p-2" placeholder="DB_PASSWORD">
        <input name="db_prefix" value="{{ old('db_prefix', data_get($state, 'payload.db.db_prefix')) }}" class="rounded border p-2" placeholder="DB_PREFIX (optionnel)">
        <button class="rounded bg-blue-600 py-2 text-white">Tester et continuer</button>
    </form>
</div>
@endsection
