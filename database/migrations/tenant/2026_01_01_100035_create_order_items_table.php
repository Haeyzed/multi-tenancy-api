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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->json('product_snapshot');
            $table->json('variant_snapshot')->nullable();
            $table->string('sku');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_compare_price', 12, 2)->nullable();
            $table->decimal('line_total', 12, 2);
            $table->decimal('line_discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('weight', 10, 3)->nullable();
            $table->enum('fulfillment_status', ['unfulfilled', 'partial', 'fulfilled', 'returned'])->default('unfulfilled');
            $table->integer('fulfilled_quantity')->default(0);
            $table->integer('returned_quantity')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'fulfillment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
