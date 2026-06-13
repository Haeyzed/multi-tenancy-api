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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['page_view', 'product_view', 'add_to_cart', 'remove_from_cart', 'purchase', 'search', 'wishlist_add', 'review_submit', 'checkout_start', 'checkout_complete', 'login', 'register'])->default('page_view');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            $table->index(['event_type', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
