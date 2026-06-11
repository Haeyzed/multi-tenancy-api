<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('store_addresses')) {
            return;
        }

        DB::table('store_addresses')->where('type', 'warehouse')->update(['type' => 'shipping']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE store_addresses MODIFY type VARCHAR(50) NOT NULL DEFAULT 'primary'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE store_addresses MODIFY type ENUM('warehouse', 'retail', 'return', 'billing') NOT NULL DEFAULT 'warehouse'");
        }
    }
};
