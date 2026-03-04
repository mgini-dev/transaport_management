<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->decimal('base_distance_km', 12, 2)->nullable()->after('fuel_station');
            $table->decimal('additional_distance_km', 12, 2)->nullable()->after('base_distance_km');
            $table->decimal('total_distance_km', 12, 2)->nullable()->after('additional_distance_km');
            $table->decimal('estimated_fuel_litres', 12, 2)->nullable()->after('total_distance_km');
            $table->decimal('available_balance_litres', 12, 2)->nullable()->after('estimated_fuel_litres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'base_distance_km',
                'additional_distance_km',
                'total_distance_km',
                'estimated_fuel_litres',
                'available_balance_litres',
            ]);
        });
    }
};
