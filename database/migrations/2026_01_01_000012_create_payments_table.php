<?php

use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->integer('amount'); // in cents
            $table->string('currency')->default('USD');
            $table->string('status')->default(PaymentStatus::Pending->value);
            $table->string('payment_provider')->default(PaymentProvider::Stripe->value);
            $table->string('provider_payment_id')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->string('payment_method_last4')->nullable();
            $table->string('payment_method_brand')->nullable();
            $table->text('failure_message')->nullable();
            $table->integer('refunded_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
