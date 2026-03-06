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
            <nav class="flex gap-3 text-sm">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.access.roles.index') }}">Access</a>
                <a href="{{ route('admin.media.index') }}">Media</a>
                <a href="{{ route('admin.menus.index') }}">Menus</a>
                <a href="{{ route('admin.exports.index') }}">Exports</a>
            </nav>
            @auth
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="rounded bg-red-600 px-3 py-1 text-white">Logout</button></form>
            @endauth
        </div>
    </header>

    @if(session('status'))
        <div id="admin-toast" class="mx-auto mt-4 max-w-6xl rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">{{ session('status') }}</div>
    @endif

    <main class="mx-auto max-w-6xl p-6">@yield('content')</main>
</div>
<script>
setTimeout(() => { const toast = document.getElementById('admin-toast'); if (toast) toast.remove(); }, 3500);
document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
        if (form.dataset.noLoading === '1') return;
        const btn = form.querySelector('button[type="submit"],button:not([type])');
        if (btn) { btn.dataset.originalText = btn.textContent; btn.textContent = 'Processing…'; btn.disabled = true; }
    }, { once: true });
});
</script>
</body>
</html>
