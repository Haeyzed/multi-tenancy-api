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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->enum('category', ['basic', 'allowance', 'bonus', 'overtime', 'tax', 'insurance', 'loan', 'other'])->default('other');
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_percentage')->default(false);
            $table->decimal('default_value', 12, 2)->nullable();
            $table->text('formula')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
