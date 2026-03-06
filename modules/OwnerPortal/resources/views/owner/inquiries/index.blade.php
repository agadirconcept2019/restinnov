@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">Inquiries</h2>
<table class="min-w-full rounded border bg-white text-sm"><tbody>
@foreach($inquiries as $inquiry)
<tr class="border-b"><td class="p-2">{{ $inquiry->property->translated()?->title }}</td><td class="p-2">{{ $inquiry->status }}</td><td class="p-2"><a class="text-blue-600" href="{{ route('owner.inquiries.show',$inquiry) }}">View</a></td></tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $inquiries->links() }}</div>
@endsection
