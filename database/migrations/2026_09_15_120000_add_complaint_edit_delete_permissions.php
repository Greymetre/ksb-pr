<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
return new class extends Migration {
    private array $permissions = ['complaint_edit', 'complaint_delete'];
    public function up(): void {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) return;
        $roleId = DB::table('roles')->where('name','superadmin')->where('guard_name','users')->value('id');
        foreach ($this->permissions as $name) {
            $id = DB::table('permissions')->where('name',$name)->where('guard_name','users')->value('id');
            if (!$id) $id = DB::table('permissions')->insertGetId(['name'=>$name,'guard_name'=>'users','created_at'=>now(),'updated_at'=>now()]);
            if ($roleId && !DB::table('role_has_permissions')->where('permission_id',$id)->where('role_id',$roleId)->exists()) DB::table('role_has_permissions')->insert(['permission_id'=>$id,'role_id'=>$roleId]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void {
        if (!Schema::hasTable('permissions')) return;
        $ids=DB::table('permissions')->where('guard_name','users')->whereIn('name',$this->permissions)->pluck('id');
        if(Schema::hasTable('role_has_permissions'))DB::table('role_has_permissions')->whereIn('permission_id',$ids)->delete();
        if(Schema::hasTable('model_has_permissions'))DB::table('model_has_permissions')->whereIn('permission_id',$ids)->delete();
        DB::table('permissions')->whereIn('id',$ids)->delete(); app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
