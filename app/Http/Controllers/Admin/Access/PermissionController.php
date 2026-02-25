<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\Core\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $search = request('q');

        $permissions = Permission::query()
            ->when($search, fn ($q, $value) => $q->where('slug', 'like', "%{$value}%"))
            ->orderBy('slug')
            ->paginate(40)
            ->withQueryString();

        return view('admin.access.permissions', compact('permissions', 'search'));
    }
}
