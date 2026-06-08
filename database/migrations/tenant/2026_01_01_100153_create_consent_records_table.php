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
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('consent_type', ['marketing', 'analytics', 'cookies', 'terms_of_service', 'privacy_policy', 'data_sharing', 'sms', 'email'])->default('terms_of_service');
            $table->string('version');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('given_at')->useCurrent();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'consent_type', 'version']);
            $table->index(['consent_type', 'given_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
