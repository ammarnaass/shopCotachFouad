<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'locale' => SetLocale::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Exclude instant-buy AJAX endpoints from CSRF for guest shoppers
        // (they are protected by rate limiting + validation)
        $middleware->validateCsrfTokens(except: [
            'instant/*',
            '*/instant/*',
            'instant-buy/*',
            '*/instant-buy/*',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson() || $request->ajax(),
        );
    })->create();
