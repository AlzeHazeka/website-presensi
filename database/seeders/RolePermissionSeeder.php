<?php

namespace Database\Seeders;

use App\Support\RoleAccess;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (! RoleAccess::spatieReady()) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = Permissions::all();

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $roleSuperAdmin = Role::findOrCreate(Roles::SUPER_ADMIN, 'web');
        $roleAdmin = Role::findOrCreate(Roles::ADMIN, 'web');
        $roleKaryawan = Role::findOrCreate(Roles::KARYAWAN, 'web');

        $roleSuperAdmin->syncPermissions($permissions);

        $roleAdmin->syncPermissions(Permissions::forLegacyRole(Roles::ADMIN));
        $roleKaryawan->syncPermissions(Permissions::forLegacyRole(Roles::KARYAWAN));
    }
}
