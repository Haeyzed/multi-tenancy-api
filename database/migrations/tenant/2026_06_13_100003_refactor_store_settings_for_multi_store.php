<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('store_settings', 'store_id')) {
            Schema::table('store_settings', function (Blueprint $table) {
                $table->foreignUuid('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('store_settings') && Schema::hasTable('stores')) {
            $legacyRows = DB::table('store_settings')->get();

            foreach ($legacyRows as $row) {
                $slug = $row->store_slug ?: Str::slug($row->store_name ?: 'default-store');
                $storeId = (string) Str::uuid();

                DB::table('stores')->insert([
                    'id' => $storeId,
                    'name' => $row->store_name ?: 'Default Store',
                    'slug' => $slug,
                    'tagline' => $row->tagline,
                    'description' => $row->description,
                    'logo_media_id' => $row->logo_media_id,
                    'favicon_media_id' => $row->favicon_media_id,
                    'is_primary' => true,
                    'is_active' => ! ($row->maintenance_mode ?? false),
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('store_settings')
                    ->where('id', $row->id)
                    ->update(['store_id' => $storeId]);
            }
        }

        Schema::table('store_settings', function (Blueprint $table) {
            $table->uuid('store_id')->nullable(false)->change();
            $table->unique('store_id');
            $table->dropColumn(['store_name', 'store_slug', 'tagline', 'description', 'logo_media_id', 'favicon_media_id']);
            $table->boolean('catalog_visible')->default(true)->after('maintenance_message');
            $table->boolean('checkout_enabled')->default(true)->after('catalog_visible');
            $table->boolean('guest_checkout_allowed')->default(true)->after('checkout_enabled');
            $table->boolean('shipping_enabled')->default(true)->after('guest_checkout_allowed');
            $table->boolean('cod_enabled')->default(true)->after('shipping_enabled');
            $table->boolean('card_payment_enabled')->default(false)->after('cod_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropUnique(['store_id']);
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'store_id',
                'catalog_visible',
                'checkout_enabled',
                'guest_checkout_allowed',
                'shipping_enabled',
                'cod_enabled',
                'card_payment_enabled',
            ]);
            $table->string('store_name')->nullable();
            $table->string('store_slug')->nullable()->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('logo_media_id')->nullable();
            $table->foreignId('favicon_media_id')->nullable();
        });
    }
};
