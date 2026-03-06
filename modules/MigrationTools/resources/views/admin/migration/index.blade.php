@extends('layouts.admin')
@section('content')
<h1>Migration tools</h1>
<form method="post" action="{{ route('admin.migration.store') }}" enctype="multipart/form-data">
@csrf
<select name="source_type"><option value="wxr_xml">WXR XML</option><option value="wp_rest">WP REST</option><option value="csv">CSV</option></select>
<input name="base_url" placeholder="https://wordpress.site">
<input type="file" name="wxr_file">
<input type="file" name="csv_file">
<label><input type="checkbox" name="dry_run" value="1"> Dry run</label>
<label><input type="checkbox" name="download_media" value="1"> Download media</label>
<label><input type="checkbox" name="create_redirects" value="1"> Create redirects</label>
<select name="overwrite_strategy"><option value="skip_if_exists">Skip existing</option><option value="update_if_exists">Update existing</option></select>
<button type="submit">Create import run</button>
</form>

<table>
<tr><th>ID</th><th>Source</th><th>Status</th><th>Summary</th></tr>
@foreach($runs as $run)
<tr><td><a href="{{ route('admin.migration.show',$run) }}">{{ $run->id }}</a></td><td>{{ $run->source_type }}</td><td>{{ $run->status }}</td><td>{{ json_encode($run->summary) }}</td></tr>
@endforeach
</table>
{{ $runs->links() }}
@endsection
