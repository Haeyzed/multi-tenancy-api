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
        Schema::create('employee_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('benefit_type', ['health_insurance', 'life_insurance', 'dental', 'vision', 'retirement', 'transport', 'housing', 'meal', 'education', 'other'])->default('other');
            $table->string('provider')->nullable();
            $table->string('policy_number')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('premium_amount', 12, 2)->nullable();
            $table->decimal('employee_contribution', 12, 2)->default(0);
            $table->decimal('employer_contribution', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_benefits');
    }
};
