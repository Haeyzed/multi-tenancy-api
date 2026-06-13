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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['main', 'retail', 'return', 'dropship', 'fulfillment'])->default('main');
            $table->json('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('timezone')->default('UTC');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
