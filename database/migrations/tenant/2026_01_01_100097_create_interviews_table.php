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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('application_stages');
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->enum('location', ['online', 'office', 'phone'])->default('online');
            $table->string('meeting_link')->nullable();
            $table->json('interviewer_ids')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show', 'rescheduled'])->default('scheduled');
            $table->text('feedback')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
