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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('website_url')->nullable();
            $table->string('tax_number')->nullable();
            $table->enum('payment_terms', ['immediate', 'net_15', 'net_30', 'net_60', 'net_90'])->default('net_30');
            $table->string('currency')->default('USD');
            $table->json('address')->nullable();
            $table->text('bank_details')->nullable();
            $table->decimal('rating', 2, 1)->default(3.0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
