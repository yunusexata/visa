<?php

use App\Http\Middleware\AccessPermission;
use App\Http\Middleware\EsignRequired;
use App\Http\Middleware\Role;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'access_permission' => AccessPermission::class,
            'role' => Role::class,
            'esign_required' => EsignRequired::class,
        ])

            ->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR |
                    Request::HEADER_X_FORWARDED_HOST |
                    Request::HEADER_X_FORWARDED_PORT |
                    Request::HEADER_X_FORWARDED_PROTO |
                    Request::HEADER_X_FORWARDED_PREFIX
            );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        )->report(function (Throwable $e) {
            logger()->error('LIVEWIRE EXCEPTION', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'url' => request()->fullUrl(),
                'headers' => request()->headers->all(),
            ]);
        });
    })->create();
