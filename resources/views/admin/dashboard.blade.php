@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Dashboard</h2>
<div class="grid gap-4 md:grid-cols-4">
    <div class="rounded border bg-white p-4"><div class="text-sm text-slate-500">Install lock</div><div class="font-semibold">{{ $installLocked ? 'Actif' : 'Absent' }}</div></div>
    <div class="rounded border bg-white p-4"><div class="text-sm text-slate-500">Modules</div><div class="font-semibold">{{ $enabledModulesCount }}/{{ $modulesCount }}</div></div>
    <div class="rounded border bg-white p-4"><div class="text-sm text-slate-500">Locale courante</div><div class="font-semibold">{{ $locale }}</div></div>
    <div class="rounded border bg-white p-4"><div class="text-sm text-slate-500">Liens rapides</div><div class="font-semibold"><a class="text-blue-600" href="{{ route('cms.home') }}">Voir le site</a></div></div>
</div>
<div class="mt-4 flex gap-6">
    <a class="text-blue-600" href="{{ route('admin.real-estate.properties.index') }}">Manage Real Estate</a>
    <a class="text-blue-600" href="{{ route('admin.cms-pages.pages.index') }}">Manage CMS Pages</a>
    <a class="text-blue-600" href="{{ route('admin.forms.submissions.index') }}">Manage Forms</a>
    <a class="text-blue-600" href="{{ route('admin.blog.posts.index') }}">Manage Blog</a>
    <a class="text-blue-600" href="{{ route('admin.redirects.index') }}">Manage Redirects</a>
    <a class="text-blue-600" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
</div>
@endsection
