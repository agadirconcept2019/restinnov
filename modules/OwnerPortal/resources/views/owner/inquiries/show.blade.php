@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Inquiry #{{ $inquiry->id }}</h2>
<p>{{ $inquiry->first_name }} {{ $inquiry->last_name }} - {{ $inquiry->email }}</p>
<form method="POST" action="{{ route('owner.inquiries.reply',$inquiry) }}" class="mt-3">@csrf<textarea name="message" class="w-full rounded border p-2" required></textarea><button class="mt-2 rounded bg-blue-600 px-3 py-2 text-white">Reply</button></form>
<form method="POST" action="{{ route('owner.inquiries.notes.store',$inquiry) }}" class="mt-3">@csrf<textarea name="note" class="w-full rounded border p-2" required></textarea><button class="mt-2 rounded bg-slate-900 px-3 py-2 text-white">Add note</button></form>
<ul class="mt-4 list-disc pl-5">@foreach($notes as $note)<li>{{ $note->note }} ({{ $note->visibility }})</li>@endforeach</ul>
@endsection
