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
    ->withMiddleware(function (Middleware $middleware): void {
        //
        // Register route middleware aliases
        $middleware->alias([
            'auth'              => CustomAuthenticate::class,
            'hasaccount'        => HasAccountMiddleware::class,
            'hassessionaccount' => HasSessionAccountMiddleware::class,
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
    })->create();
