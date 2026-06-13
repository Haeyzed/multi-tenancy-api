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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'partially_refunded', 'returned', 'failed'])->default('pending');
            $table->enum('payment_status', ['pending', 'authorized', 'paid', 'partially_paid', 'refunded', 'failed', 'void'])->default('pending');
            $table->enum('fulfillment_status', ['unfulfilled', 'partial', 'fulfilled'])->default('unfulfilled');
            $table->string('currency')->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_refunded', 12, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_method')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('staff_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('source', ['web', 'api', 'pos', 'import', 'marketplace'])->default('web');
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'payment_status']);
            $table->index(['user_id', 'placed_at']);
            $table->index(['order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
