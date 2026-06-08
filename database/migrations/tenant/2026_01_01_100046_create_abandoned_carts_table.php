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
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('email_opened')->default(false);
            $table->boolean('email_clicked')->default(false);
            $table->timestamp('recovered_at')->nullable();
            $table->decimal('recovery_amount', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
