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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance', 'remote'])->default('full_time');
            $table->foreignId('location_id')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->integer('vacancies_count')->default(1);
            $table->enum('status', ['draft', 'open', 'paused', 'closed', 'filled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('closing_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
