<?php

declare(strict_types=1);

namespace App\Core\Authorization\Controllers;

use App\Core\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

final class PermissionController
{
    public function index(): JsonResponse
    {
        $permissions = Permission::all();

        return ApiResponse::success($permissions, 'Permisos obtenidos');
    }
}
