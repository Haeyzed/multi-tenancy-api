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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->nullable()->unique();
            $table->enum('type', ['online', 'retail', 'popup', 'franchise', 'kiosk', 'hybrid'])
                ->default('online');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('website_url')->nullable();
            $table->json('address')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->json('opening_hours')->nullable();
            $table->foreignId('manager_id')->nullable();
            $table->foreignId('logo_media_id')->nullable();
            $table->foreignId('favicon_media_id')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
