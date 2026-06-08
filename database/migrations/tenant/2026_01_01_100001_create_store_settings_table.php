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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('store_slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('logo_media_id')->nullable();
            $table->foreignId('favicon_media_id')->nullable();
            $table->string('primary_color')->default('#3B82F6');
            $table->string('secondary_color')->default('#10B981');
            $table->string('currency')->default('USD');
            $table->string('default_language')->default('en');
            $table->string('timezone')->default('UTC');
            $table->enum('weight_unit', ['kg', 'g', 'lb', 'oz'])->default('kg');
            $table->enum('dimension_unit', ['cm', 'm', 'in', 'ft'])->default('cm');
            $table->boolean('tax_included_in_prices')->default(false);
            $table->boolean('auto_invoice')->default(true);
            $table->string('order_number_prefix')->default('ORD-');
            $table->integer('order_number_start')->default(1000);
            $table->string('meta_title_template')->nullable();
            $table->string('meta_description_template')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->text('custom_scripts')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
