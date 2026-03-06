@extends('layouts.admin')

@section('content')
<h2 class="mb-4 text-2xl font-semibold">Roles & Permissions</h2>
<div class="space-y-4">
    @foreach($roles as $role)
        <section class="rounded border bg-white p-4">
            <h3 class="font-semibold">{{ $role->name }} ({{ $role->slug }})</h3>
            <form method="POST" action="{{ route('admin.access.roles.permissions.update', $role) }}" class="mt-2 grid grid-cols-2 gap-2">
                @csrf
                @foreach($permissions as $permission)
                    <label class="text-sm"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))> {{ $permission->slug }}</label>
                @endforeach
                <div class="col-span-2"><button class="rounded bg-blue-600 px-3 py-1 text-white">Save permissions</button></div>
            </form>
        </section>
    @endforeach
</div>

<h2 class="mb-4 mt-8 text-2xl font-semibold">Assign roles to users</h2>
<div class="space-y-2">
    @foreach($users as $user)
        <form method="POST" action="{{ route('admin.access.users.roles.update', $user) }}" class="rounded border bg-white p-3">
            @csrf
            <p class="mb-2 font-medium">{{ $user->name }} ({{ $user->email }})</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach($roles as $role)
                    <label class="text-sm"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($user->roles->contains('id', $role->id) || $user->role === $role->slug)> {{ $role->slug }}</label>
                @endforeach
            </div>
            <button class="mt-2 rounded bg-slate-700 px-3 py-1 text-white">Update roles</button>
        </form>
    @endforeach
</div>
@endsection
