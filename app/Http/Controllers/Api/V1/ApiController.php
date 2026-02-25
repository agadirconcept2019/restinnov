<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    protected function resolveLocale(): string
    {
        $locale = request('locale');

        if (! $locale) {
            $header = request()->header('Accept-Language', '');
            $locale = strtolower(substr($header, 0, 2));
        }

        $supported = config('locales.supported', ['en', 'fr', 'es']);
        if (! in_array($locale, $supported, true)) {
            $locale = config('locales.default', 'en');
        }

        app()->setLocale($locale);

        return $locale;
    }
}
