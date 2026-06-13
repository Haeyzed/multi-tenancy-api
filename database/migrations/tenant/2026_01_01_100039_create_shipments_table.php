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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('carrier');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->enum('status', ['pending', 'label_created', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'returned'])->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('label_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
