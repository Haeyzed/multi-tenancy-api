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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')->constrained('pos_registers')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('employees');
            $table->decimal('opening_amount', 12, 2)->default(0);
            $table->decimal('expected_closing', 12, 2)->nullable();
            $table->decimal('actual_closing', 12, 2)->nullable();
            $table->decimal('difference', 12, 2)->default(0);
            $table->text('difference_reason')->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['active', 'closed', 'forced_closed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
