@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Blog Categories</h2>
<form method="POST" action="{{ route('admin.blog.categories.store') }}" class="mb-4 grid gap-2 rounded border bg-white p-4">
    @csrf
    <input name="slug" class="rounded border p-2" placeholder="slug" required>
    <input name="name_en" class="rounded border p-2" placeholder="Name EN" required>
    <input name="name_fr" class="rounded border p-2" placeholder="Name FR">
    <input name="name_es" class="rounded border p-2" placeholder="Name ES">
    <input name="sort_order" type="number" class="rounded border p-2" value="0">
    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button class="rounded bg-blue-600 px-4 py-2 text-white">Create</button>
</form>
<table class="min-w-full rounded border bg-white text-sm">
    <tr class="bg-slate-100"><th class="p-2 text-left">Slug</th><th>Name</th><th>Active</th><th></th></tr>
    @foreach($categories as $category)
        <tr class="border-t">
            <td class="p-2">{{ $category->slug }}</td>
            <td>{{ $category->translated()?->name }}</td>
            <td>{{ $category->is_active ? 'yes' : 'no' }}</td>
            <td>
                <form method="POST" action="{{ route('admin.blog.categories.destroy', $category) }}">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
            </td>
        </tr>
    @endforeach
</table>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection
