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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->json('check_in_location')->nullable();
            $table->enum('check_in_method', ['biometric', 'mobile_app', 'web', 'manual', 'api', 'card'])->default('mobile_app');
            $table->foreignId('check_in_photo_media_id')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->json('check_out_location')->nullable();
            $table->enum('check_out_method', ['biometric', 'mobile_app', 'web', 'manual', 'api', 'card'])->nullable();
            $table->foreignId('check_out_photo_media_id')->nullable();
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->integer('break_duration_minutes')->default(0);
            $table->decimal('total_working_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'holiday', 'remote', 'field_work'])->default('present');
            $table->text('notes')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_manual_entry')->default(false);
            $table->text('manual_entry_reason')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
