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
        Schema::create('pick_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('pick_list_id')->constrained('pick_lists')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items');
            $table->integer('quantity');
            $table->integer('picked_qty')->default(0);
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->enum('status', ['pending', 'picked', 'packed', 'shipped', 'short', 'damaged'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pick_list_items');
    }
};
