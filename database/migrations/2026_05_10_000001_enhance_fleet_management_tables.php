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
        // 1. Enhance Fleets table
        Schema::table('fleets', function (Blueprint $table) {
            $table->decimal('current_odometer', 12, 2)->default(0)->after('capacity_tons');
            $table->decimal('last_service_odometer', 12, 2)->default(0)->after('current_odometer');
            $table->unsignedInteger('oil_change_interval_km')->default(5000)->after('last_service_odometer');
            $table->decimal('next_service_due_km', 12, 2)->default(5000)->after('oil_change_interval_km');
            $table->string('vehicle_type')->nullable()->after('fleet_code');
            $table->decimal('fuel_consumption_benchmark', 8, 2)->nullable()->comment('Liters per 100km')->after('next_service_due_km');
            $table->text('notes')->nullable()->after('status');
        });

        // 2. Enhance Fuel Requisitions
        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->decimal('odometer_reading', 12, 2)->nullable()->after('fuel_price');
        });

        // 3. Create Fleet Driver History
        Schema::create('fleet_driver_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_id')->constrained('fleets')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->decimal('start_odometer', 12, 2)->default(0);
            $table->decimal('end_odometer', 12, 2)->nullable();
            $table->timestamps();
            
            $table->index(['fleet_id', 'assigned_at']);
        });

        // 4. Create Maintenance Logs
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_id')->constrained('fleets')->onDelete('cascade');
            $table->string('service_type'); // oil_change, repair, inspection, tire_rotation, etc.
            $table->decimal('odometer_reading', 12, 2);
            $table->decimal('cost', 14, 2)->default(0);
            $table->timestamp('performed_at');
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fleet_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('fleet_driver_history');
        
        Schema::table('fuel_requisitions', function (Blueprint $table) {
            $table->dropColumn('odometer_reading');
        });

        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn([
                'current_odometer',
                'last_service_odometer',
                'oil_change_interval_km',
                'next_service_due_km',
                'vehicle_type',
                'fuel_consumption_benchmark',
                'notes'
            ]);
        });
    }
};
