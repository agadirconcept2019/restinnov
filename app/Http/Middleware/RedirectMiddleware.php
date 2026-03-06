<?php

namespace App\Http\Middleware;

use App\Models\Core\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $path = '/'.ltrim($request->path(), '/');

        $redirect = Redirect::query()->where('from_path', $path)->where('is_active', true)->first();
        if (! $redirect) {
            return $next($request);
        }

        $target = $this->safeTarget($redirect->to_url);
        if (! $target) {
            return $next($request);
        }

        if ($request->getQueryString() && ! str_contains($target, '?')) {
            $target .= '?'.$request->getQueryString();
        }

        DB::table('redirects')->where('id', $redirect->id)->update(['hits' => DB::raw('hits + 1')]);

        return redirect()->to($target, in_array($redirect->status_code, [301, 302], true) ? $redirect->status_code : 301);
    }

    private function safeTarget(string $toUrl): ?string
    {
        $host = parse_url($toUrl, PHP_URL_HOST);
        if (! $host) {
            return url('/'.ltrim($toUrl, '/'));
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $host === $appHost ? $toUrl : null;
    }
}
