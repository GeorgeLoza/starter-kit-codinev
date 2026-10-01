<?php

declare(strict_types=1);

namespace App\Core\Authorization\Controllers;

use App\Core\Http\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class RoleController
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return ApiResponse::success($roles, 'Roles obtenidos');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'sanctum']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return ApiResponse::success($role->load('permissions'), 'Rol creado', 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        return ApiResponse::success($role->fresh()->load('permissions'), 'Rol actualizado');
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return ApiResponse::success(null, 'Rol eliminado');
    }

    public function assignToUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->assignRole($validated['role']);

        return ApiResponse::success(
            $user->load('roles'),
            'Rol asignado correctamente'
        );
    }

    public function removeFromUser(User $user, Role $role): JsonResponse
    {
        $user->removeRole($role);

        return ApiResponse::success(
            $user->load('roles'),
            'Rol removido correctamente'
        );
    }
}