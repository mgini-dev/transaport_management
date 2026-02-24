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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('order_number')->unique();
            $table->string('cargo_type');
            $table->text('cargo_description')->nullable();
            $table->decimal('weight_tons', 12, 2)->default(0);
            $table->decimal('agreed_price', 14, 2)->default(0);
            $table->text('origin_address');
            $table->text('destination_address');
            $table->date('expected_loading_date')->nullable();
            $table->date('expected_leaving_date')->nullable();
            $table->decimal('distance_km', 12, 2)->nullable();
            $table->decimal('estimated_fuel_litres', 12, 2)->nullable();
            $table->enum('status', ['created', 'processing', 'assigned', 'completed'])->default('created');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->enum('from_status', ['created', 'processing', 'assigned', 'completed'])->nullable();
            $table->enum('to_status', ['created', 'processing', 'assigned', 'completed']);
            $table->foreignId('changed_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->string('fleet_code')->unique();
            $table->string('plate_number')->unique();
            $table->decimal('capacity_tons', 12, 2)->default(0);
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_id')->nullable()->constrained('fleets')->nullOnDelete();
            $table->string('name');
            $table->string('license_number')->unique();
            $table->string('mobile_number');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('order_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('fleet_id')->constrained('fleets');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->unsignedInteger('leg_sequence')->default(1);
            $table->text('origin_address');
            $table->text('destination_address');
            $table->decimal('distance_km', 12, 2)->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fuel_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('fleet_id')->constrained('fleets');
            $table->foreignId('requested_by')->constrained('users');
            $table->string('fuel_station');
            $table->decimal('additional_litres', 12, 2);
            $table->decimal('fuel_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->string('payment_channel');
            $table->string('payment_account');
            $table->enum('status', [
                'submitted',
                'supervisor_approved',
                'supervisor_rejected',
                'accountant_approved',
                'accountant_rejected',
            ])->default('submitted');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('supervisor_remarks')->nullable();
            $table->timestamp('supervisor_reviewed_at')->nullable();
            $table->foreignId('accountant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('accountant_remarks')->nullable();
            $table->timestamp('accountant_reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fuel_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('fleet_id')->constrained('fleets');
            $table->decimal('remaining_litres', 12, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('loggable');
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('fuel_balances');
        Schema::dropIfExists('fuel_requisitions');
        Schema::dropIfExists('order_legs');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('fleets');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('customers');
    }
};
