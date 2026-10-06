<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Gift stock ledger. promotional_gifts.quantity stays the current stock; every
| change is recorded here: opening (gift created), add (stock added on the Gift
| List), activity (issued when a promotional activity is completed, negative).
| The quantity already on existing gifts becomes their opening stock.
*/
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('promotional_gifts', 'opening_stock')) {
            Schema::table('promotional_gifts', function (Blueprint $table) {
                $table->unsignedInteger('opening_stock')->default(0)->after('quantity');
            });
        }

        Schema::create('promotional_gift_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotional_gift_id')->index();
            $table->string('type', 20)->index();
            // Signed: positive = stock in, negative = stock out
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->unsignedBigInteger('promotional_activity_id')->nullable()->index();
            $table->string('remark', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        DB::table('promotional_gifts')->update(['opening_stock' => DB::raw('quantity')]);

        $now = now();
        DB::table('promotional_gifts')->orderBy('id')->each(function ($gift) use ($now) {
            DB::table('promotional_gift_stock_movements')->insert([
                'promotional_gift_id' => $gift->id,
                'type' => 'opening',
                'quantity' => (int) $gift->quantity,
                'balance_after' => (int) $gift->quantity,
                'remark' => 'Opening stock',
                'created_by' => $gift->created_by,
                'created_at' => $gift->created_at ?? $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotional_gift_stock_movements');

        if (Schema::hasColumn('promotional_gifts', 'opening_stock')) {
            Schema::table('promotional_gifts', function (Blueprint $table) {
                $table->dropColumn('opening_stock');
            });
        }
    }
};
