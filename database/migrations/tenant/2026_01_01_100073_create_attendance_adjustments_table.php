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
        Schema::create('attendance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->string('field_changed');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->text('reason');
            $table->foreignId('adjusted_by')->constrained('users');
            $table->timestamp('adjusted_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_adjustments');
    }
};
