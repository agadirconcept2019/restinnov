@extends('layouts.admin')
@section('content')
<h1>Email Log #{{ $log->id }}</h1>
<ul>
<li>Template: {{ $log->template_key }}</li>
<li>Recipient: {{ $log->to_email_masked }}</li>
<li>Status: {{ $log->status }}</li>
<li>Attempts: {{ $log->attempts }}</li>
<li>Subject: {{ $log->subject }}</li>
<li>Error: {{ $log->last_error_excerpt }}</li>
<li>Queued: {{ $log->queued_at }}</li>
<li>Sent: {{ $log->sent_at }}</li>
<li>Failed: {{ $log->failed_at }}</li>
</ul>
@endsection
