<?php

declare(strict_types=1);

use App\Core\Auth\Controllers\AuthController;
use App\Core\Authorization\Controllers\PermissionController;
use App\Core\Authorization\Controllers\RoleController;
use App\Core\Http\Controllers\HealthController;
use App\Core\Users\Controllers\UserController;
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
    // Users
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')->name('api.users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users.view')->name('api.users.show');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')->name('api.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')->name('api.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')->name('api.users.destroy');

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

    // Asignar/quitar rol a usuario
    Route::post('/users/{user}/roles', [RoleController::class, 'assignToUser'])
        ->middleware('permission:roles.update')->name('api.users.roles.assign');
    Route::delete('/users/{user}/roles/{role}', [RoleController::class, 'removeFromUser'])
        ->middleware('permission:roles.update')->name('api.users.roles.remove');
});