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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('reviewer_id')->constrained('employees');
            $table->date('review_period_start');
            $table->date('review_period_end');
            $table->enum('type', ['annual', 'quarterly', 'probation', 'adhoc'])->default('annual');
            $table->enum('status', ['draft', 'pending', 'completed', 'acknowledged'])->default('draft');
            $table->decimal('overall_rating', 3, 2)->nullable(); // 1.00 - 5.00
            $table->text('summary')->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->json('goals')->nullable();
            $table->json('development_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
