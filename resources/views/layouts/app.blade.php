<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body class="bg-slate-50 text-slate-900">
<header class="border-b bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between p-4">
        <a href="{{ route('home') }}" class="font-bold">{{ config('app.name') }}</a>
        <nav class="flex gap-3 text-sm">
            <a href="{{ route('properties.index') }}">Our Properties</a>
            <a href="{{ route('services') }}">Services</a>
            <a href="{{ route('faq') }}">FAQ</a>
            <a href="{{ route('blog.index') }}">Blog</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-6xl p-6">
    @if(session('status'))
        <div class="mb-4 rounded bg-emerald-100 p-3 text-emerald-800">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
