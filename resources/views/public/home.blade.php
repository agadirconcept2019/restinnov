@extends('layouts.public')

@section('content')
<div class="rounded-lg border bg-white p-6">
    <h1 class="text-3xl font-bold">RestInnov CMS</h1>
    <p class="mt-2 text-slate-700">Phase 1 opérationnelle : Core modulaire, installateur web, shell admin, i18n (en/fr/es).</p>
    <div class="mt-4 flex gap-3">
        <a href="{{ route('install.step1') }}" class="rounded bg-blue-600 px-4 py-2 text-white">Lancer /install</a>
        <a href="{{ route('admin.login') }}" class="rounded bg-slate-900 px-4 py-2 text-white">Login admin</a>
    </div>
</div>
@endsection
