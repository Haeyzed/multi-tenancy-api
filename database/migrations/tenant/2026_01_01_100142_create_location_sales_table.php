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
        Schema::create('location_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->integer('order_count')->default(0);
            $table->integer('item_count')->default(0);
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('card_sales', 12, 2)->default(0);
            $table->decimal('other_sales', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_sales');
    }
};
