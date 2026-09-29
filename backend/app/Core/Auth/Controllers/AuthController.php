<?php

declare(strict_types=1);

namespace App\Core\Auth\Controllers;

use App\Core\Auth\Actions\LoginAction;
use App\Core\Auth\Requests\LoginRequest;
use App\Core\Auth\Resources\UserResource;
use App\Core\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController
{
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device') ?? 'web'
        );

        return ApiResponse::success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Inicio de sesión exitoso');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'Usuario autenticado'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Sesión cerrada correctamente');
    }
}
