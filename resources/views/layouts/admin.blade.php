<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · {{ config('app.name') }}</title>
    @if(!app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between p-4">
            <h1 class="font-semibold">Administration</h1>
            @auth
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="rounded bg-red-600 px-3 py-1 text-white">Logout</button></form>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-6xl p-6">@yield('content')</main>
</div>
</body>
</html>
