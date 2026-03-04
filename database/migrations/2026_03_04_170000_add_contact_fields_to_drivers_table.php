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
        Schema::table('drivers', function (Blueprint $table) {
            $table->text('driver_address')->nullable()->after('mobile_number');
            $table->string('contact1_name')->nullable()->after('driver_address');
            $table->string('contact1_phone')->nullable()->after('contact1_name');
            $table->text('contact1_address')->nullable()->after('contact1_phone');
            $table->string('contact2_name')->nullable()->after('contact1_address');
            $table->string('contact2_phone')->nullable()->after('contact2_name');
            $table->text('contact2_address')->nullable()->after('contact2_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'driver_address',
                'contact1_name',
                'contact1_phone',
                'contact1_address',
                'contact2_name',
                'contact2_phone',
                'contact2_address',
            ]);
        });
    }
};

