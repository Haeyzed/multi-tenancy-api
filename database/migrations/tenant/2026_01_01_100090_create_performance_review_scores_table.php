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
        Schema::create('performance_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('performance_reviews')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('performance_review_criteria')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('comments')->nullable();
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestamps();
            $table->unique(['review_id', 'criteria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_review_scores');
    }
};
