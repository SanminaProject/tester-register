<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases used by Spatie laravel-permission.
        // (Laravel 11 no longer uses Http\Kernel.php, so we must register them here.)
        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $isApiRequest = static function (Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        };

        $errorResponse = static function (Request $request, int $status, string $message, array $extra = []) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json(array_merge([
                'success' => false,
                'message' => $message,
                'code' => $status,
            ], $extra), $status);
        };

        $exceptions->render(function (ValidationException $e, Request $request) use ($errorResponse) {
            return $errorResponse(
                $request,
                422,
                $e->validator->errors()->first() ?: 'Validation failed',
                ['errors' => $e->errors()]
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($errorResponse) {
            return $errorResponse($request, 401, 'Unauthenticated');
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($errorResponse) {
            return $errorResponse($request, 403, 'Forbidden');
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($errorResponse) {
            return $errorResponse($request, 403, 'Forbidden');
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) use ($errorResponse) {
            return $errorResponse($request, 404, 'Resource not found');
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($errorResponse) {
            return $errorResponse($request, 405, 'Method not allowed');
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($errorResponse) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$status] ?? 'HTTP error');

            return $errorResponse($request, $status, $message);
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($errorResponse) {
            $message = config('app.debug') ? $e->getMessage() : 'Internal server error';

            return $errorResponse($request, 500, $message);
        });
    })->create();
