<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branch_holiday', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('holiday_id');
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();

            $table->unique(['holiday_id', 'branch_id']);
            $table->foreign('holiday_id')->references('id')->on('holidays')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });

        $now = now();
        $validBranchIds = DB::table('branches')->pluck('id')->map(fn ($id) => (int) $id)->all();
        DB::table('holidays')
            ->select('id', 'branch')
            ->whereNotNull('branch')
            ->orderBy('id')
            ->chunkById(500, function ($holidays) use ($now, $validBranchIds) {
                $rows = [];

                foreach ($holidays as $holiday) {
                    foreach (array_filter(array_map('trim', explode(',', (string) $holiday->branch))) as $branchId) {
                        if (ctype_digit($branchId) && in_array((int) $branchId, $validBranchIds, true)) {
                            $rows[] = [
                                'holiday_id' => $holiday->id,
                                'branch_id' => (int) $branchId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if ($rows) {
                    DB::table('branch_holiday')->insertOrIgnore($rows);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('branch_holiday');
    }
};
