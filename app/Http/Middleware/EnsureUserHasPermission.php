<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (str_starts_with($permission, 'role:')) {
            abort_unless($user->hasRole(substr($permission, 5)), 403);

            return $next($request);
        }

        abort_unless($user->hasPermission($permission), 403);

        return $next($request);
    }
}
