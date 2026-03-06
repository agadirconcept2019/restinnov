@extends('layouts.admin')

@section('content')
    <h1>Failed Jobs</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Queue</th>
                <th>Failed At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($failedJobs as $job)
                <tr>
                    <td>{{ $job->id }}</td>
                    <td>{{ $job->queue }}</td>
                    <td>{{ $job->failed_at }}</td>
                    <td>
                        <form method="post" action="{{ route('admin.ops.jobs.retry') }}">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">
                            <button type="submit">Retry</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No failed jobs.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $failedJobs->links() }}
@endsection
