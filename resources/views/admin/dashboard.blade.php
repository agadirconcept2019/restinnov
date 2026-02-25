@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Dashboard</h2>
<div class="grid gap-4 md:grid-cols-4">
    <div class="rounded border bg-white p-4">
        <div class="text-sm text-slate-500">Install lock</div>
        <div class="font-semibold">{{ $installLocked ? 'Actif' : 'Absent' }}</div>
    </div>
    <div class="rounded border bg-white p-4">
        <div class="text-sm text-slate-500">Modules</div>
        <div class="font-semibold">{{ $enabledModulesCount }}/{{ $modulesCount }}</div>
    </div>
    <div class="rounded border bg-white p-4">
        <div class="text-sm text-slate-500">Locale courante</div>
        <div class="font-semibold">{{ $locale }}</div>
    </div>
    <div class="rounded border bg-white p-4">
        <div class="text-sm text-slate-500">Liens rapides</div>
        <div class="font-semibold"><a class="text-blue-600" href="{{ route('home') }}">Voir le site</a></div>
    </div>
</div>
<div class="mt-4">
    <a class="text-blue-600" href="{{ route('admin.real-estate.properties.index') }}">Manage Real Estate</a>
</div>
@endsection
