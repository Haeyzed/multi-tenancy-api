<?php

use App\Enums\Central\InvoiceStatus;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default(InvoiceStatus::Draft->value);
            $table->integer('amount_due'); // in cents
            $table->integer('amount_paid')->default(0);
            $table->integer('amount_remaining');
            $table->string('currency')->default('USD');
            $table->timestamp('billing_period_start');
            $table->timestamp('billing_period_end');
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('latest_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['latest_invoice_id']);
        });

        Schema::dropIfExists('invoices');
    }
};
