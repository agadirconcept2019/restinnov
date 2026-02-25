@extends('layouts.admin')
@section('content')
<h1>Exports</h1>
<p>Use artisan commands for CSV/JSON exports:</p>
<pre>php artisan migration:export pages --format=json
php artisan migration:export posts --format=csv
php artisan migration:export properties --format=csv</pre>
@endsection
