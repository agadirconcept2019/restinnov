<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('locales.supported', ['en', 'fr', 'es']);
        $fallback = config('locales.fallback', 'en');

        $locale = $request->route('locale') ?: config('locales.default', $fallback);
        if (! in_array($locale, $supported, true)) {
            $locale = $fallback;
        }

        App::setLocale($locale);
        View::share('currentLocale', $locale);
        View::share('supportedLocales', $supported);

        return $next($request);
    }
}
