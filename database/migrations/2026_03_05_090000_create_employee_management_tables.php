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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 32)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('phone_number', 30);
            $table->string('email', 150)->unique();
            $table->text('address');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'separated', 'other']);
            $table->date('date_of_birth');
            $table->string('position_title', 150);
            $table->date('date_employed');
            $table->unsignedSmallInteger('contract_duration_months')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('bank_account_name', 150);
            $table->string('bank_account_number', 80);
            $table->string('bank_branch', 150);
            $table->decimal('salary_net', 14, 2);
            $table->string('tin_number', 80)->nullable();
            $table->string('nssf_number', 80)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->enum('employment_status', ['active', 'probation', 'on_leave', 'suspended', 'terminated', 'resigned', 'contract_expired'])->default('active');
            $table->date('status_effective_date')->nullable();
            $table->text('status_note')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employment_status', 'date_employed'], 'employees_status_employed_idx');
            $table->index(['first_name', 'last_name'], 'employees_names_idx');
            $table->index(['phone_number'], 'employees_phone_idx');
        });

        Schema::create('employee_next_of_kins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('phone_number', 30);
            $table->text('address');
            $table->timestamps();

            $table->index(['employee_id', 'is_primary'], 'employee_kins_primary_idx');
        });

        Schema::create('employee_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('certificate_name', 255)->nullable();
            $table->string('file_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
        });

        Schema::create('employee_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('remarks')->nullable();
            $table->date('effective_date')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'created_at'], 'employee_status_history_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_status_histories');
        Schema::dropIfExists('employee_certificates');
        Schema::dropIfExists('employee_next_of_kins');
        Schema::dropIfExists('employees');
    }
};

