<?php

namespace App\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Core\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::query()->latest()->paginate(30);

        return view('admin.redirects.index', compact('redirects'));
    }

    public function store(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', 'unique:redirects,from_path'],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', 'in:301,302'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $redirect = Redirect::query()->create($data + ['is_active' => (bool) ($data['is_active'] ?? true)]);
        $auditLogger->log('redirect.created', $redirect, ['from_path' => $redirect->from_path]);

        return back()->with('status', 'Redirect created.');
    }

    public function update(Request $request, Redirect $redirect, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', 'in:301,302'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $redirect->update($data + ['is_active' => (bool) ($data['is_active'] ?? false)]);
        $auditLogger->log('redirect.updated', $redirect, ['to_url' => $redirect->to_url]);

        return back()->with('status', 'Redirect updated.');
    }
}
