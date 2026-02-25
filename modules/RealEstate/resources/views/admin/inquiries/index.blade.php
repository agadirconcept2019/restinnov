@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Property inquiries</h2>
<table class="min-w-full rounded border bg-white text-sm"><tr class="bg-slate-100"><th class="p-2 text-left">Name</th><th>Email</th><th>Status</th><th></th></tr>
@foreach($inquiries as $inq)
<tr class="border-t"><td class="p-2">{{ $inq->first_name }} {{ $inq->last_name }}</td><td>{{ $inq->email }}</td><td>{{ $inq->status }}</td><td><a class="text-blue-600" href="{{ route('admin.real-estate.inquiries.show',$inq) }}">View</a></td></tr>
@endforeach
</table>
<div class="mt-4">{{ $inquiries->links() }}</div>
@endsection
