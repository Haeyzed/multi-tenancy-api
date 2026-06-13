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
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('loyalty_programs')->cascadeOnDelete();
            $table->string('name');
            $table->integer('min_points')->default(0);
            $table->integer('max_points')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->json('benefits')->nullable(); // Free shipping, early access, etc.
            $table->string('color')->nullable();
            $table->foreignId('icon_media_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
