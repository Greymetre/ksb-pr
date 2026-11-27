<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
| Reports > User > Promotional Activity Report (screen + Excel).
| Given to superadmin only; other roles can be granted it from the roles screen.
*/
return new class extends Migration
{
    private string $permissionName = 'promotional_activity_report';

    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('name', $this->permissionName)
            ->where('guard_name', 'users')
            ->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $this->permissionName,
                'guard_name' => 'users',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $superAdminRoleId = DB::table('roles')
            ->where('name', 'superadmin')
            ->where('guard_name', 'users')
            ->value('id');

        if ($superAdminRoleId && !DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->where('role_id', $superAdminRoleId)
            ->exists()) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id' => $superAdminRoleId,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('name', $this->permissionName)
            ->where('guard_name', 'users')
            ->value('id');

        if ($permissionId) {
            if (Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            }
            if (Schema::hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            }
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
