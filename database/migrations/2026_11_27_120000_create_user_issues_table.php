<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| User Management > User Issues.
| An issue is raised either from the User List calling popup (source = calling,
| linked to the calling remark) or created directly on the User Issues screen
| (source = manual). Starts as pending -> in_progress -> resolved.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_issues')) {
            Schema::create('user_issues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('issue_date')->index();
                $table->text('description');
                $table->string('status', 20)->default('pending')->index();
                $table->string('source', 20)->default('manual');
                $table->unsignedBigInteger('calling_remark_id')->nullable()->unique();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('solved_by')->nullable();
                $table->text('solve_remark')->nullable();
                $table->timestamp('solved_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_issues');
    }
};
