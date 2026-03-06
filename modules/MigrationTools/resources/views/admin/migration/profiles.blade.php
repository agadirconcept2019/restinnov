@extends('layouts.admin')
@section('content')
<h1>Migration Profiles</h1>
<form method="post" action="{{ route('admin.migration.profiles.store') }}">@csrf
<input name="name" placeholder="Profile name" required>
<select name="source_type"><option value="wp_rest">WP REST</option><option value="csv">CSV</option><option value="wxr_xml">WXR</option></select>
<input name="base_url" placeholder="https://wordpress.example">
<input name="properties_endpoint" placeholder="/wp-json/wp/v2/properties">
<select name="delta_strategy"><option value="full">full</option><option value="since_last_run">since_last_run</option><option value="since_datetime">since_datetime</option></select>
<select name="overwrite_strategy"><option value="skip_if_exists">skip_if_exists</option><option value="update_if_exists">update_if_exists</option></select>
<h4>CSV field mapping</h4>
<input name="field_mapping[title]" placeholder="column for title">
<input name="field_mapping[slug]" placeholder="column for slug">
<input name="field_mapping[base_price_per_night]" placeholder="column for price">
<input name="field_mapping[max_guests]" placeholder="column for max guests">
<button type="submit">Create profile</button>
</form>
<table><tr><th>Name</th><th>Source</th><th>Delta</th><th>Last success</th></tr>
@foreach($profiles as $profile)
<tr><td>{{ $profile->name }}</td><td>{{ $profile->source_type }}</td><td>{{ $profile->delta_strategy }}</td><td>{{ $profile->last_successful_run_at }}</td></tr>
@endforeach
</table>
{{ $profiles->links() }}
@endsection
