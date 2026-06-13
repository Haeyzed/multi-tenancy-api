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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('tier')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('price_monthly'); // smallest currency unit (kobo for NGN)
            $table->integer('price_yearly');  // smallest currency unit (kobo for NGN)
            $table->string('currency')->default('NGN');
            $table->integer('trial_days')->default(14);
            $table->integer('sort_order')->default(0);
            $table->json('features'); // marketing/display copy only — enforcement uses plan_features table
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
