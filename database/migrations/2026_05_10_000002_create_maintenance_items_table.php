<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('maintenance_log_id')->constrained()->cascadeOnDelete();
            $blueprint->string('category'); // e.g., Tires, Brakes, Engine, Battery
            $blueprint->string('description');
            $blueprint->decimal('cost', 15, 2)->default(0);
            $blueprint->decimal('installed_at_km', 15, 2);
            $blueprint->decimal('lifespan_km', 15, 2)->nullable();
            $blueprint->decimal('next_due_km', 15, 2)->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_items');
    }
};
