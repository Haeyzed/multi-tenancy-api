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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('USD');
            $table->enum('provider', ['stripe', 'paypal', 'cod', 'bank_transfer', 'wallet', 'gift_card'])->default('stripe');
            $table->string('provider_payment_id')->nullable();
            $table->enum('payment_method', ['card', 'bank_transfer', 'cash', 'wallet', 'crypto'])->default('card');
            $table->string('card_last4')->nullable();
            $table->string('card_brand')->nullable();
            $table->text('failure_reason')->nullable();
            $table->enum('status', ['pending', 'authorized', 'captured', 'failed', 'refunded', 'partially_refunded', 'void'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
