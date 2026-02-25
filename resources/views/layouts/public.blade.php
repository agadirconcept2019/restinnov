<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @if(!app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 text-slate-900">
<header class="border-b bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between p-4">
        <a href="{{ route('home') }}" class="font-semibold">{{ config('app.name') }}</a>
        <div class="flex items-center gap-2 text-sm">
            @foreach($supportedLocales ?? config('locales.supported') as $locale)
                <a class="rounded px-2 py-1 {{ app()->getLocale()===$locale?'bg-slate-900 text-white':'bg-slate-100' }}" href="{{ $locale === config('locales.default') ? url('/') : url('/'.$locale) }}">{{ strtoupper($locale) }}</a>
            @endforeach
            <a href="{{ route('admin.login') }}" class="rounded bg-slate-100 px-2 py-1">Admin</a>
        </div>
    </div>
</header>
<main class="mx-auto max-w-6xl p-6">
    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 p-3 text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if(session('status'))
        <div class="mb-4 rounded bg-emerald-100 p-3 text-emerald-700">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
