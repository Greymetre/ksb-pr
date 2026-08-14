<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'new_dealer_target_edit',
        'new_dealer_target_delete',
    ];

    public function up()
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        $superAdminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

        foreach ($this->permissions as $permissionName) {
            $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');

            if (! $permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permissionName,
                    'guard_name' => 'users',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($superAdminRoleId && ! DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $superAdminRoleId)
                ->exists()) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminRoleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')->whereIn('name', $this->permissions)->pluck('id');
        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        }
        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        }
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
