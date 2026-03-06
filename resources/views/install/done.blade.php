@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold">Installation complete</h1>
<p>Le CMS est prêt.</p>
<a href="{{ route('admin.dashboard') }}" class="text-blue-600">Go to dashboard</a>
@endsection
