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
        Schema::create('pick_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->enum('status', ['pending', 'picking', 'picked', 'packed', 'shipped', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('picker_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('packer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pick_lists');
    }
};
