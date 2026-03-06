@extends('layouts.admin')
@section('content')
<h1>Email Templates</h1>
<form method="post" action="{{ route('admin.communications.templates.store') }}">@csrf
<input name="key" placeholder="booking.confirmed" required>
<input name="locale" placeholder="en" required>
<input name="subject" placeholder="Subject" required>
<textarea name="body_html" rows="5" placeholder="<p>Body with {invoice_number}</p>" required></textarea>
<label><input type="checkbox" name="is_active" value="1" checked> Active</label>
<button>Save template</button>
</form>

<form method="post" action="{{ route('admin.communications.templates.send-test') }}">@csrf
<input name="to" type="email" placeholder="test@example.com" required>
<input name="key" placeholder="booking.confirmed" required>
<input name="locale" placeholder="en">
<button>Send test</button>
</form>

<table><tr><th>Key</th><th>Locale</th><th>Active</th><th>Updated</th></tr>
@foreach($templates as $template)
<tr><td>{{ $template->key }}</td><td>{{ $template->locale }}</td><td>{{ $template->is_active ? 'yes' : 'no' }}</td><td>{{ $template->updated_at }}</td></tr>
@endforeach
</table>
{{ $templates->links() }}
@endsection
