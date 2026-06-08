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
        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('converted_to_order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['converted_to_order_id']);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
    }
};
