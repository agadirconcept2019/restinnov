@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-2 text-lg font-semibold">Étape 6 · Finalisation</h2>
    <p>Installation terminée. Le lock installateur est actif.</p>
    <div class="mt-4 flex gap-3">
        <a href="{{ route('cms.home') }}" class="rounded bg-slate-200 px-4 py-2">Aller au site</a>
        <a href="{{ route('admin.login') }}" class="rounded bg-blue-600 px-4 py-2 text-white">Aller au login admin</a>
    </div>
</div>
@endsection
