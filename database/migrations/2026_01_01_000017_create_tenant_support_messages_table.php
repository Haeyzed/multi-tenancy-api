<?php

use App\Enums\Central\MessageSenderType;
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
        Schema::create('tenant_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tenant_support_tickets')->cascadeOnDelete();
            $table->string('sender_type')->default(MessageSenderType::User->value);
            $table->foreignId('sender_id')->nullable();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_support_messages');
    }
};
