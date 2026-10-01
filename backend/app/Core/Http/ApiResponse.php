<?php

declare(strict_types=1);

namespace App\Core\Http;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operación realizada correctamente',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(
        string $message = 'Ha ocurrido un error',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    public static function validation(
        mixed $errors,
        string $message = 'Error de validación'
    ): JsonResponse {
        return self::error($message, $errors, 422);
    }

    public static function unauthorized(
        string $message = 'No autenticado'
    ): JsonResponse {
        return self::error($message, null, 401);
    }

    public static function forbidden(
        string $message = 'No tienes permiso para realizar esta acción'
    ): JsonResponse {
        return self::error($message, null, 403);
    }

    public static function notFound(
        string $message = 'Recurso no encontrado'
    ): JsonResponse {
        return self::error($message, null, 404);
    }

    public static function paginated(
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        string $message = 'Recursos obtenidos',
        ?callable $transformer = null
    ): JsonResponse {
        $items = $transformer
            ? collect($paginator->items())->map($transformer)->values()
            : $paginator->items();

        return self::success([
            'items' => $items,
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], $message);
    }
}
