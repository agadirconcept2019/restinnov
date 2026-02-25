@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold">Admin dashboard</h1>
<p>Shell d'administration prêt à être étendu par les modules.</p>
<form method="post" action="{{ route('logout') }}" class="mt-4">@csrf<button class="rounded bg-red-600 px-3 py-2 text-white">Logout</button></form>
@endsection
