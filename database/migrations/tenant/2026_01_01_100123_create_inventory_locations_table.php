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
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('warehouse_zones')->nullOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('available_qty')->default(0);
            $table->integer('reorder_point')->default(10);
            $table->integer('reorder_qty')->default(50);
            $table->timestamps();
            $table->unique(['product_id', 'variant_id', 'warehouse_id', 'bin_id'], 'inv_loc_prod_var_wh_bin_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
