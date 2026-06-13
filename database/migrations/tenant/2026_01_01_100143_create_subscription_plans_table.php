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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('billing_interval', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'biannual', 'yearly'])->default('monthly');
            $table->integer('interval_count')->default(1);
            $table->decimal('price', 12, 2);
            $table->integer('trial_days')->default(0);
            $table->decimal('setup_fee', 12, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
