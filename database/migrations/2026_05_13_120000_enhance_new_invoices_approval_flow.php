<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('new_invoices')) {
            Schema::table('new_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('new_invoices', 'points')) {
                    $table->decimal('points', 15, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn('new_invoices', 'approval_status')) {
                    $table->tinyInteger('approval_status')->default(0)->index()->after('points');
                }
                if (!Schema::hasColumn('new_invoices', 'approval_remark')) {
                    $table->text('approval_remark')->nullable()->after('approval_status');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_ss_by')) {
                    $table->unsignedBigInteger('approved_ss_by')->nullable()->index()->after('approval_remark');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_ss_at')) {
                    $table->timestamp('approved_ss_at')->nullable()->after('approved_ss_by');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_sales_by')) {
                    $table->unsignedBigInteger('approved_sales_by')->nullable()->index()->after('approved_ss_at');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_sales_at')) {
                    $table->timestamp('approved_sales_at')->nullable()->after('approved_sales_by');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_ho_by')) {
                    $table->unsignedBigInteger('approved_ho_by')->nullable()->index()->after('approved_sales_at');
                }
                if (!Schema::hasColumn('new_invoices', 'approved_ho_at')) {
                    $table->timestamp('approved_ho_at')->nullable()->after('approved_ho_by');
                }
                if (!Schema::hasColumn('new_invoices', 'rejected_by')) {
                    $table->unsignedBigInteger('rejected_by')->nullable()->index()->after('approved_ho_at');
                }
                if (!Schema::hasColumn('new_invoices', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
            });
        }

        if (!Schema::hasTable('new_invoice_approval_logs')) {
            Schema::create('new_invoice_approval_logs', function (Blueprint $table) {
                $table->id();
                $table->date('log_date')->nullable();
                $table->unsignedBigInteger('new_invoice_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('status_type')->nullable();
                $table->tinyInteger('from_status')->nullable();
                $table->tinyInteger('to_status')->nullable();
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }

        $permissions = [
            'new_invoice_access',
            'new_invoice_create',
            'new_invoice_edit',
            'new_invoice_delete',
            'new_invoice_export',
            'new_invoice_approve_ss',
            'new_invoice_approve_sales',
            'new_invoice_approve_ho',
            'new_invoice_reject',
        ];

        foreach ($permissions as $permission) {
            $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

            if (!$permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permission,
                    'guard_name' => 'users',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $superAdminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

            if ($superAdminRoleId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $superAdminRoleId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $superAdminRoleId,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('new_invoice_approval_logs');

        if (Schema::hasTable('new_invoices')) {
            Schema::table('new_invoices', function (Blueprint $table) {
                foreach ([
                    'points',
                    'approval_status',
                    'approval_remark',
                    'approved_ss_by',
                    'approved_ss_at',
                    'approved_sales_by',
                    'approved_sales_at',
                    'approved_ho_by',
                    'approved_ho_at',
                    'rejected_by',
                    'rejected_at',
                ] as $column) {
                    if (Schema::hasColumn('new_invoices', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
