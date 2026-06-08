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
        Schema::create('application_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('application_stages')->cascadeOnDelete();
            $table->enum('status', ['pending', 'passed', 'failed', 'skipped'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('conducted_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_stage_history');
    }
};
