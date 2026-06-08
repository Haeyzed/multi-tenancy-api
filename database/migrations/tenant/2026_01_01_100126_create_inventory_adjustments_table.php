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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->enum('type', ['damage', 'expiry', 'theft', 'found', 'correction', 'transfer_in', 'transfer_out', 'initial_stock'])->default('correction');
            $table->integer('quantity');
            $table->text('reason');
            $table->foreignUuid('approved_by')->constrained('employees');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
