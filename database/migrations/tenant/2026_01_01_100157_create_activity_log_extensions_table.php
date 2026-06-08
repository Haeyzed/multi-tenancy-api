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
        Schema::create('activity_log_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_log_id')->constrained('activity_log')->cascadeOnDelete();
            $table->enum('event_category', ['pricing', 'inventory', 'order', 'user', 'product', 'payment', 'shipping', 'hr', 'system'])->default('system');
            $table->enum('business_impact', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->json('notified_users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log_extensions');
    }
};
