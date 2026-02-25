@extends('layouts.install')

@section('content')
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Étape 1 · Vérification serveur</h2>
    <ul class="space-y-2">
        @foreach($checks as $check)
            <li class="flex justify-between border-b pb-1"><span>{{ $check['label'] }}</span><span class="font-semibold {{ $check['passed'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $check['passed'] ? 'PASS' : 'FAIL' }}</span></li>
        @endforeach
    </ul>
    @if($allPassed)
        <a href="{{ route('install.step2') }}" class="mt-4 inline-block rounded bg-blue-600 px-4 py-2 text-white">Continuer</a>
    @else
        <p class="mt-4 text-red-600">Corrigez les prérequis avant de continuer.</p>
    @endif
</div>
@endsection
