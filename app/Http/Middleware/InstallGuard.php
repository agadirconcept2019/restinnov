<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallGuard
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('install*') && file_exists(storage_path('app/install.lock'))) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
