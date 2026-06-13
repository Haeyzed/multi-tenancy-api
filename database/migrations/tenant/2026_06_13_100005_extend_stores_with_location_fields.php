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
        Schema::table('stores', function (Blueprint $table) {
            $table->enum('type', ['online', 'retail', 'popup', 'franchise', 'kiosk', 'hybrid'])
                ->default('online')
                ->after('code');
            $table->string('timezone')->default('UTC')->after('address');
            $table->string('currency', 3)->default('USD')->after('timezone');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('currency');
            $table->json('opening_hours')->nullable()->after('tax_rate');
            $table->foreignId('manager_id')->nullable()->after('opening_hours')->constrained('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'type',
                'timezone',
                'currency',
                'tax_rate',
                'opening_hours',
                'manager_id',
            ]);
        });
    }
};
