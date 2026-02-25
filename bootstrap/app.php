<?php

use App\Http\Middleware\InstallGuard;
use App\Http\Middleware\RedirectMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            require base_path('routes/admin.php');
            require base_path('routes/install.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->alias([
            'set.locale' => SetLocale::class,
            'install.guard' => InstallGuard::class,
            'redirect.core' => RedirectMiddleware::class,
        ]);

        $middleware->append(RedirectMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
