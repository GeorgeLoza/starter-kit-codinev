<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.view', 'users.create', 'users.update', 'users.delete',
            // Roles
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            // Permissions
            'permissions.view', 'permissions.assign',
            // Audit
            'audit.view',
            // Files
            'files.view', 'files.upload', 'files.delete',
            // Notifications
            'notifications.view', 'notifications.send',
            // Settings
            'settings.view', 'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'sanctum');
        }

        // super-admin: todos los permisos
        $superAdmin = Role::findOrCreate('super-admin', 'sanctum');
        $superAdmin->syncPermissions(Permission::all());

        // admin: todo menos gestión de roles/permisos
        $admin = Role::findOrCreate('admin', 'sanctum');
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.update', 'users.delete',
            'audit.view',
            'files.view', 'files.upload', 'files.delete',
            'notifications.view', 'notifications.send',
            'settings.view',
        ]);

        // user: permisos básicos
        $user = Role::findOrCreate('user', 'sanctum');
        $user->syncPermissions([
            'users.view',
            'files.view', 'files.upload',
            'notifications.view',
        ]);
    }
}