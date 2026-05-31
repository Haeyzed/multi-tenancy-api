<?php

use App\Enums\Central\EventTriggeredBy;
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
        Schema::create('subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('event_type');
            $table->foreignUuid('from_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignUuid('to_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('triggered_by')->default(EventTriggeredBy::System->value);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
    }
};
