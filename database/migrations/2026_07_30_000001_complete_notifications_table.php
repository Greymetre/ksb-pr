<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('read')->default(false)->index()->after('data');
            $table->string('model', 100)->default('general')->index()->after('read');
            $table->unsignedBigInteger('model_id')->nullable()->index()->after('model');
            $table->string('delivery_status', 30)->default('pending')->index()->after('model_id');
            $table->timestamp('sent_at')->nullable()->after('delivery_status');
            $table->text('failure_reason')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['read']);
            $table->dropIndex(['model']);
            $table->dropIndex(['model_id']);
            $table->dropIndex(['delivery_status']);
            $table->dropColumn(['read', 'model', 'model_id', 'delivery_status', 'sent_at', 'failure_reason']);
        });
    }
};
