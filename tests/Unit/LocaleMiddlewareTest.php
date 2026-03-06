<?php

namespace Tests\Unit;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    public function test_locale_middleware_applies_supported_locale_and_fallback(): void
    {
        $middleware = new SetLocale;

        $requestSupported = Request::create('/fr', 'GET');
        $requestSupported->setRouteResolver(function () {
            return new class {
                public function parameter(string $key): string
                {
                    return 'fr';
                }
            };
        });

        $middleware->handle($requestSupported, fn () => response('ok'));
        $this->assertSame('fr', app()->getLocale());

        $requestUnsupported = Request::create('/xx', 'GET');
        $requestUnsupported->setRouteResolver(function () {
            return new class {
                public function parameter(string $key): string
                {
                    return 'xx';
                }
            };
        });

        $middleware->handle($requestUnsupported, fn () => response('ok'));
        $this->assertSame(config('locales.fallback'), app()->getLocale());
    }
}
