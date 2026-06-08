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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('live_chat_sessions')->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'agent', 'bot', 'system'])->default('customer');
            $table->foreignUuid('sender_id')->nullable();
            $table->longText('message');
            $table->foreignId('attachment_media_id')->nullable();
            $table->timestamps();
            $table->index(['session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
