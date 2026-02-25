@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Étape 3 · Configuration application</h2>
    <form method="POST" action="{{ route('install.step3.store') }}" class="grid gap-3">
        @csrf
        <input name="app_name" value="{{ old('app_name', config('app.name')) }}" class="rounded border p-2" placeholder="APP_NAME" required>
        <input name="app_url" value="{{ old('app_url', config('app.url')) }}" class="rounded border p-2" placeholder="APP_URL" required>
        <input name="app_timezone" value="{{ old('app_timezone', config('app.timezone')) }}" class="rounded border p-2" placeholder="APP_TIMEZONE" required>
        <select name="default_locale" class="rounded border p-2">@foreach(config('locales.supported') as $loc)<option value="{{ $loc }}" @selected(old('default_locale', config('locales.default'))===$loc)>{{ strtoupper($loc) }}</option>@endforeach</select>
        <input name="mail_from_name" value="{{ old('mail_from_name') }}" class="rounded border p-2" placeholder="MAIL_FROM_NAME (optionnel)">
        <input name="mail_from_address" value="{{ old('mail_from_address') }}" class="rounded border p-2" placeholder="MAIL_FROM_ADDRESS (optionnel)">
        <button class="rounded bg-blue-600 py-2 text-white">Enregistrer et continuer</button>
    </form>
</div>
@endsection
