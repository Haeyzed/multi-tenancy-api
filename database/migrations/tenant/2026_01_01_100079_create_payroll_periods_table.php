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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->enum('status', ['draft', 'processing', 'paid', 'closed'])->default('draft');
            $table->enum('type', ['monthly', 'biweekly', 'weekly', 'semi_monthly'])->default('monthly');
            $table->timestamps();
            $table->unique(['start_date', 'end_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
