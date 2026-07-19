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
            $table->string('certificate_number')->nullable()->after('license_number');
            $table->string('certificate_file_path')->nullable()->after('certificate_number');
            $table->date('certificate_expiry_date')->nullable()->after('certificate_file_path');
            $table->string('license_file_path')->nullable()->after('license_number');
            $table->date('license_expiry_date')->nullable()->after('license_file_path');
            $table->string('license_renewed_place')->nullable()->after('license_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_number',
                'certificate_file_path',
                'certificate_expiry_date',
                'license_file_path',
                'license_expiry_date',
                'license_renewed_place',
            ]);
        });
    }
};
