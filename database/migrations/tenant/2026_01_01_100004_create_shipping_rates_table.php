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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->string('name');
            $table->enum('based_on', ['weight', 'price', 'items'])->default('price');
            $table->json('conditions')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free_shipping_above')->default(false);
            $table->decimal('free_above_amount', 10, 2)->nullable();
            $table->integer('delivery_days_min')->nullable();
            $table->integer('delivery_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
