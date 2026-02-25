<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (file_exists(storage_path('app/install.lock')) && $request->is('install*')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
