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
        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('supplier_returns')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('purchase_order_items');
            $table->integer('returned_qty');
            $table->decimal('refund_amount', 12, 2);
            $table->enum('condition', ['damaged', 'defective', 'wrong_item', 'expired', 'other'])->default('other');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
    }
};
