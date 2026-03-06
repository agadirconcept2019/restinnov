<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\Core\Permission;
use App\Models\Core\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.access.roles', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->paginate(20),
            'users' => User::query()->orderBy('name')->paginate(20, ['*'], 'users_page'),
            'permissions' => Permission::query()->orderBy('slug')->get(),
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('status', 'Role permissions updated.');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $roleIds = $validated['roles'] ?? [];
        $user->roles()->sync($roleIds);
        $user->update(['role' => $user->roles()->first()?->slug ?? $user->role]);

        return back()->with('status', 'User roles updated.');
    }
}
