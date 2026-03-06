@extends('layouts.admin')
@section('content')
<h1>Migration Runs</h1>
<p><a href="{{ route('admin.migration.profiles') }}">Manage profiles</a></p>
<form method="post" action="{{ route('admin.migration.run') }}">@csrf
<select name="profile_id">@foreach($profiles as $profile)<option value="{{ $profile->id }}">{{ $profile->name }}</option>@endforeach</select>
<label><input type="checkbox" name="dry_run" value="1"> Dry run</label>
<input type="datetime-local" name="since_datetime">
<button type="submit">Run profile</button>
</form>

<form method="post" action="{{ route('admin.migration.wizard.csv-mapping') }}" enctype="multipart/form-data">@csrf
<h3>CSV mapping wizard</h3>
<input type="file" name="csv_file" required>
<input name="mapping[title]" placeholder="column for title">
<input name="mapping[slug]" placeholder="column for slug">
<input name="mapping[base_price_per_night]" placeholder="column for price">
<input name="mapping[max_guests]" placeholder="column for max guests">
<button type="submit">Validate mapping</button>
</form>

<table><tr><th>ID</th><th>Profile</th><th>Status</th><th>Summary</th></tr>
@foreach($runs as $run)
<tr><td><a href="{{ route('admin.migration.runs.show',$run) }}">{{ $run->id }}</a></td><td>{{ $run->profile?->name }}</td><td>{{ $run->status }}</td><td>{{ json_encode($run->summary) }}</td></tr>
@endforeach
</table>
{{ $runs->links() }}
@endsection
