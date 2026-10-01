<?php

declare(strict_types=1);

use App\Core\Auth\Controllers\AuthController;
use App\Core\Authorization\Controllers\PermissionController;
use App\Core\Authorization\Controllers\RoleController;
use App\Core\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('api.health');

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Roles
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')->name('api.roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')->name('api.roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')->name('api.roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')->name('api.roles.destroy');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.view')->name('api.permissions.index');

    // Asignar rol a usuario
    Route::post('/users/{user}/roles', [RoleController::class, 'assignToUser'])
        ->middleware('permission:roles.update')->name('api.users.roles.assign');
    Route::delete('/users/{user}/roles/{role}', [RoleController::class, 'removeFromUser'])
        ->middleware('permission:roles.update')->name('api.users.roles.remove');
});