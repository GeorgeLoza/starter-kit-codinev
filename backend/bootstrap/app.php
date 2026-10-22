<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : '/login';
        });

        $middleware->alias([
            'role'              => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'        => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Validación (debe ir primero para no ser atrapada por el Throwable genérico)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Error de validación',
                    'errors'  => $e->errors(),
                ], 422);
            }

            return null;
        });

        // 2. No autenticado
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado. Debes iniciar sesión.',
                    'errors'  => null,
                ], 401);
            }

            return null;
        });

        // 3. Ruta no encontrada
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recurso no encontrado',
                    'errors'  => null,
                ], 404);
            }

            return null;
        });

        // 4. Sin permisos (Spatie)
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para realizar esta acción',
                    'errors'  => null,
                ], 403);
            }

            return null;
        });

        // 5. Catch-all para el resto (debe ir al final)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            // Excluir ValidationException (ya manejada arriba)
            if ($e instanceof ValidationException) {
                return null;
            }

            $status = 500;
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            }

            $message = $e->getMessage() ?: 'Error del servidor';

            // En producción, no exponer detalles internos
            if (! config('app.debug') && $status === 500) {
                $message = 'Error del servidor';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => null,
            ], $status);
        });
    })->create();