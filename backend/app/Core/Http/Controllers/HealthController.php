<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers;

use App\Core\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app'      => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
        ];

        $healthy = ! in_array(false, array_column($checks, 'ok'), true);

        return ApiResponse::success(
            [
                'status'  => $healthy ? 'ok' : 'degraded',
                'checks'  => $checks,
                'version' => config('app.version', '0.1.0'),
            ],
            $healthy ? 'Servicio operativo' : 'Servicio con problemas',
            $healthy ? 200 : 503
        );
    }

    private function checkApp(): array
    {
        return [
            'ok'      => true,
            'name'    => config('app.name'),
            'env'     => config('app.env'),
            'php'     => PHP_VERSION,
            'laravel' => app()->version(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return [
                'ok'       => true,
                'driver'   => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName(),
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . now()->timestamp;
            Cache::put($key, 'ok', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'ok'    => $value === 'ok',
                'store' => config('cache.default'),
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}