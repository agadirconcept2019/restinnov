<?php

namespace App\Modules\OwnerPortal\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class OwnerController extends Controller
{
    public function index()
    {
        $owners = User::query()->where('role', 'owner')->paginate(20);

        return view('ownerportal::admin.owners.index', compact('owners'));
    }
}
