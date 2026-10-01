<?php

declare(strict_types=1);

namespace App\Core\Users\Controllers;

use App\Core\Http\ApiResponse;
use App\Core\Users\Actions\CreateUserAction;
use App\Core\Users\Actions\DeleteUserAction;
use App\Core\Users\Actions\UpdateUserAction;
use App\Core\Users\DTOs\CreateUserData;
use App\Core\Users\DTOs\UpdateUserData;
use App\Core\Users\Requests\StoreUserRequest;
use App\Core\Users\Requests\UpdateUserRequest;
use App\Core\Users\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $search  = $request->string('search')->toString();

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return ApiResponse::paginated(
            $users,
            'Usuarios obtenidos',
            fn(User $user) => (new UserResource($user))->resolve()
        );
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');

        return ApiResponse::success(
            new UserResource($user),
            'Usuario obtenido'
        );
    }

    public function store(
        StoreUserRequest $request,
        CreateUserAction $action
    ): JsonResponse {
        $user = $action->execute(
            CreateUserData::fromArray($request->validated())
        );

        $user->load('roles');

        return ApiResponse::success(
            new UserResource($user),
            'Usuario creado',
            201
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): JsonResponse {
        $user = $action->execute(
            $user,
            UpdateUserData::fromArray($request->validated())
        );

        $user->load('roles');

        return ApiResponse::success(
            new UserResource($user),
            'Usuario actualizado'
        );
    }

    public function destroy(User $user, DeleteUserAction $action): JsonResponse
    {
        $action->execute($user);

        return ApiResponse::success(null, 'Usuario eliminado');
    }
}
