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
        Schema::create('customer_loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('loyalty_programs')->cascadeOnDelete();
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->nullOnDelete();
            $table->integer('total_points')->default(0);
            $table->integer('available_points')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->string('card_number')->nullable()->unique();
            $table->timestamps();
            $table->unique(['user_id', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_loyalty_accounts');
    }
};
