<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi();
        $middleware->web(prepend: [
            SecurityHeaders::class,
        ]);
        $middleware->api(prepend: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return $request->expectsJson() || $request->is(
                'products',
                'products/*',
                'currencies',
                'currencies/*',
                'auth',
                'auth/*',
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is(
                'products',
                'products/*',
                'currencies',
                'currencies/*',
                'auth',
                'auth/*',
            )) {
                return null;
            }

            $previous = $exception->getPrevious();

            $message = $previous instanceof ModelNotFoundException
                ? class_basename($previous->getModel()).' not found.'
                : 'Not found.';

            return response()->json(['message' => $message], 404);
        });
    })->create();
