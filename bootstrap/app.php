<?php

use App\Http\Middleware\CustomAuthenticate;
use App\Http\Middleware\HasAccountMiddleware;
use App\Http\Middleware\HasSessionAccountMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
//     ->withMiddleware(function (Middleware $middleware): void {

//     // 1. GLOBAL MIDDLEWARE
//     $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
//     $middleware->append(\App\Http\Middleware\TrustProxies::class);

//     // 2. MIDDLEWARE GROUPS
//     $middleware->group(['web','api'], [
//         \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
//     ]);

//     // 3. ROUTE MIDDLEWARE ALIASES
//     $middleware->alias([
//         'auth'              => CustomAuthenticate::class,
//         'hasaccount'        => HasAccountMiddleware::class,
//         'hassessionaccount' => HasSessionAccountMiddleware::class,
//         'cors'              => \App\Http\Middleware\CorsMiddleware::class,
//     ]);
// })

    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        //
        // Register route middleware aliases
        $middleware->alias([
            'auth'              => CustomAuthenticate::class,
            'hasaccount'        => HasAccountMiddleware::class,
            'hassessionaccount' => HasSessionAccountMiddleware::class,
            'cors'              => \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'errors' => ['auth' => ['Unauthenticated.']]
                ], 401);
            }
        });
    })
    ->create();
