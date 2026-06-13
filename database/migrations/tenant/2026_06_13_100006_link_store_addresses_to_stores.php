<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('store_addresses', 'store_id')) {
            Schema::table('store_addresses', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            });
        }

        $primaryStoreId = DB::table('stores')->where('is_primary', true)->value('id')
            ?? DB::table('stores')->orderBy('created_at')->value('id');

        if ($primaryStoreId) {
            DB::table('store_addresses')->whereNull('store_id')->update(['store_id' => $primaryStoreId]);
        }

        DB::table('store_addresses')->whereNull('store_id')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE store_addresses MODIFY store_id CHAR(36) NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('store_addresses', 'store_id')) {
            Schema::table('store_addresses', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
};
