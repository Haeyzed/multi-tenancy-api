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
        Schema::create('subscription_billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('customer_subscriptions')->cascadeOnDelete();
            $table->integer('cycle_number')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'billed', 'paid', 'failed', 'skipped', 'refunded'])->default('pending');
            $table->foreignId('invoice_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['subscription_id', 'cycle_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_billing_cycles');
    }
};
