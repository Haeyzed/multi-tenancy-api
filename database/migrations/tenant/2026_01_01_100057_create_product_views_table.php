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
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('unique_views')->default(0);
            $table->integer('add_to_carts')->default(0);
            $table->integer('purchases')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
