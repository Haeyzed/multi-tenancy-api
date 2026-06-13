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
        Schema::create('offboarding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('resignation_date');
            $table->date('last_working_date');
            $table->enum('reason', ['resignation', 'termination', 'retirement', 'contract_end', 'other'])->default('resignation');
            $table->text('exit_interview_notes')->nullable();
            $table->text('handover_notes')->nullable();
            $table->enum('clearance_status', ['pending', 'in_progress', 'cleared', 'hold'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offboarding_records');
    }
};
