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
            $table->string('employee_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('personal_email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('national_id')->nullable();
            $table->foreignId('avatar_media_id')->nullable();
            $table->enum('status', ['active', 'on_leave', 'suspended', 'terminated', 'probation', 'resigned'])->default('active');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('designation_id')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance', 'remote'])->default('full_time');
            $table->foreignId('work_location_id')->nullable();
            $table->foreignId('shift_id')->nullable();
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->string('salary_currency')->default('USD');
            $table->enum('salary_type', ['monthly', 'hourly', 'weekly', 'commission'])->default('monthly');
            $table->foreignId('pay_grade_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->enum('education_level', ['high_school', 'bachelor', 'master', 'phd', 'diploma', 'other'])->nullable();
            $table->text('bio')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'department_id']);
            $table->index(['employee_code']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('employees');
    }
};
