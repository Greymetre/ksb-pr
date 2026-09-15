<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
return new class extends Migration {
    private string $permission = 'complaint_review';
    public function up(): void {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) return;
        $roleId = DB::table('roles')->where('name','superadmin')->where('guard_name','users')->value('id');
        $id = DB::table('permissions')->where('name',$this->permission)->where('guard_name','users')->value('id');
        if (!$id) $id = DB::table('permissions')->insertGetId(['name'=>$this->permission,'guard_name'=>'users','created_at'=>now(),'updated_at'=>now()]);
        if ($roleId && !DB::table('role_has_permissions')->where('permission_id',$id)->where('role_id',$roleId)->exists()) DB::table('role_has_permissions')->insert(['permission_id'=>$id,'role_id'=>$roleId]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void {
        if (!Schema::hasTable('permissions')) return;
        $id = DB::table('permissions')->where('guard_name','users')->where('name',$this->permission)->value('id');
        if (!$id) return;
        if (Schema::hasTable('role_has_permissions')) DB::table('role_has_permissions')->where('permission_id',$id)->delete();
        if (Schema::hasTable('model_has_permissions')) DB::table('model_has_permissions')->where('permission_id',$id)->delete();
        DB::table('permissions')->where('id',$id)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
