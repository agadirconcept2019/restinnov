@extends('layouts.admin')

@section('content')
    <h1>Ops Health</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <ul>
        <li>Database: {{ $dbOk ? 'ok' : 'error' }}</li>
        <li>Cache driver: {{ $cacheDriver }}</li>
        <li>Queue driver: {{ $queueDriver }}</li>
        <li>Failed jobs: {{ $failedJobsCount }}</li>
        <li>iCal feeds in failure: {{ $icalFailureCount }}</li>
    </ul>

    <p><a href="{{ route('admin.ops.jobs') }}">View failed jobs</a></p>
@endsection
