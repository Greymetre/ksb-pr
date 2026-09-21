<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
| "Odoo Sync" CRM menu: permission odoo_sync_access, given to superadmin and
| to a new "odoo" role (for the Odoo developer's CRM login).
| See odoo-integration-docs/README.md
*/
return new class extends Migration {
    private string $permission = 'odoo_sync_access';
    private string $role = 'odoo';

    public function up(): void {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) return;

        $permissionId = DB::table('permissions')->where('name', $this->permission)->where('guard_name', 'users')->value('id');
        if (!$permissionId) $permissionId = DB::table('permissions')->insertGetId(['name' => $this->permission, 'guard_name' => 'users', 'created_at' => now(), 'updated_at' => now()]);

        $odooRoleId = DB::table('roles')->where('name', $this->role)->where('guard_name', 'users')->value('id');
        if (!$odooRoleId) $odooRoleId = DB::table('roles')->insertGetId(['name' => $this->role, 'guard_name' => 'users', 'created_at' => now(), 'updated_at' => now()]);

        $superadminId = DB::table('roles')->where('name', 'superadmin')->where('guard_name', 'users')->value('id');

        foreach (array_filter([$superadminId, $odooRoleId]) as $roleId) {
            if (!DB::table('role_has_permissions')->where('permission_id', $permissionId)->where('role_id', $roleId)->exists()) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {
        if (!Schema::hasTable('permissions')) return;

        $permissionId = DB::table('permissions')->where('guard_name', 'users')->where('name', $this->permission)->value('id');
        if ($permissionId) {
            if (Schema::hasTable('role_has_permissions')) DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            if (Schema::hasTable('model_has_permissions')) DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        $roleId = DB::table('roles')->where('guard_name', 'users')->where('name', $this->role)->value('id');
        if ($roleId) {
            if (Schema::hasTable('model_has_roles')) DB::table('model_has_roles')->where('role_id', $roleId)->delete();
            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
