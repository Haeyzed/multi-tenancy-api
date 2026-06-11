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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_whatsapp')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->json('headquarters_address')->nullable();
            $table->string('website_url')->nullable();
            $table->string('default_currency', 3)->default('USD');
            $table->string('currency_symbol', 5)->default('$');
            $table->enum('currency_position', ['before', 'after'])->default('before');
            $table->string('default_timezone')->default('UTC');
            $table->string('default_language', 10)->default('en');
            $table->enum('default_weight_unit', ['kg', 'g', 'lb', 'oz'])->default('kg');
            $table->enum('default_dimension_unit', ['cm', 'm', 'in', 'ft'])->default('cm');
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->string('industry')->nullable();
            $table->string('business_type')->nullable();
            $table->json('social_links')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('terms_of_service_url')->nullable();
            $table->string('refund_policy_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
