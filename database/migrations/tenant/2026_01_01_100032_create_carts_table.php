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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('email')->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->foreignId('shipping_address_id')->nullable();
            $table->foreignId('billing_address_id')->nullable();
            $table->foreignId('shipping_method_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('abandoned_cart_email_sent')->default(false);
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('converted_to_order_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'session_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
