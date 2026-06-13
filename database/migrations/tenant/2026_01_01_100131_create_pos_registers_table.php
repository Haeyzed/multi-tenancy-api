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
        Schema::create('pos_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('location_id')->nullable();
            $table->enum('status', ['open', 'closed', 'maintenance', 'locked'])->default('closed');
            $table->foreignId('current_cashier_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('opening_amount', 12, 2)->default(0);
            $table->decimal('closing_amount', 12, 2)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_registers');
    }
};
