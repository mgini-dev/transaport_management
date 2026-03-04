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
        Schema::table('fleets', function (Blueprint $table) {
            $table->string('trailer_number')->nullable()->after('plate_number');
            $table->unique('trailer_number');
        });

        Schema::table('order_legs', function (Blueprint $table) {
            $table->string('trailer_number')->nullable()->after('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_legs', function (Blueprint $table) {
            $table->dropColumn('trailer_number');
        });

        Schema::table('fleets', function (Blueprint $table) {
            $table->dropUnique(['trailer_number']);
            $table->dropColumn('trailer_number');
        });
    }
};
