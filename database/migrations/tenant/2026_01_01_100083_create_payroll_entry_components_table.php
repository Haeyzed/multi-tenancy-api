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
        Schema::create('payroll_entry_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_entry_id')->constrained('payroll_entries')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('calculated_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entry_components');
    }
};
