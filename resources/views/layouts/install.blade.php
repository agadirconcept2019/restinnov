<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install · {{ config('app.name') }}</title>
    @if(!app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 text-slate-900">
<main class="mx-auto max-w-3xl p-6">
    <div class="mb-6 rounded-lg border bg-white p-4">
        <h1 class="text-xl font-semibold">Installateur RestInnov CMS</h1>
        <p class="text-sm text-slate-600">Assistant d'installation en 6 étapes (type WordPress)</p>
    </div>
    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 p-3 text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
