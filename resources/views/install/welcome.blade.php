@extends('layouts.app')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Install wizard - Step 1</h1>
<ul class="mb-4 list-disc pl-5">
    <li>PHP: {{ $checks['php'] }}</li>
    <li>PDO MySQL: {{ $checks['pdo_mysql'] ? 'OK' : 'KO' }}</li>
    <li>mbstring: {{ $checks['mbstring'] ? 'OK' : 'KO' }}</li>
</ul>
<form method="post" action="{{ route('install.database') }}" class="grid gap-2 rounded border bg-white p-4">
    @csrf
    <input name="db_host" placeholder="DB Host" value="127.0.0.1" required class="rounded border p-2">
    <input name="db_port" placeholder="DB Port" value="3306" required class="rounded border p-2">
    <input name="db_database" placeholder="DB Name" required class="rounded border p-2">
    <input name="db_username" placeholder="DB User" required class="rounded border p-2">
    <input name="db_password" placeholder="DB Password" type="password" class="rounded border p-2">
    <button class="rounded bg-blue-600 px-3 py-2 text-white">Continue</button>
</form>
@endsection
