<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->string('requisition_type')->default('order_based')->after('id');
            $table->text('origin_address')->nullable()->after('payment_account');
            $table->text('destination_address')->nullable()->after('origin_address');
        });

        DB::statement('ALTER TABLE fuel_requisitions MODIFY order_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Ensure no null order_id rows remain before rollback.
        DB::table('fuel_requisitions')->whereNull('order_id')->delete();

        DB::statement('ALTER TABLE fuel_requisitions MODIFY order_id BIGINT UNSIGNED NOT NULL');

        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->dropColumn(['requisition_type', 'origin_address', 'destination_address']);
        });
    }
};
