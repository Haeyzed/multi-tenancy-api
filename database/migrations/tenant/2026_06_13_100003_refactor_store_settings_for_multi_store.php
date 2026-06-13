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
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('store_settings') && Schema::hasTable('stores')) {
            $legacyRows = DB::table('store_settings')
                ->when(
                    Schema::hasColumn('store_settings', 'store_id'),
                    fn ($query) => $query->whereNull('store_id'),
                )
                ->get();

            foreach ($legacyRows as $row) {
                if (Schema::hasColumn('store_settings', 'store_id') && $row->store_id !== null) {
                    continue;
                }

                $slug = $row->store_slug ?: Str::slug($row->store_name ?: 'default-store');

                $storeId = DB::table('stores')->insertGetId([
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
            if (Schema::hasColumn('store_settings', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable(false)->change();
            }

            $indexes = collect(Schema::getIndexes('store_settings'))->pluck('name');

            if (! $indexes->contains('store_settings_store_id_unique')) {
                $table->unique('store_id');
            }

            $columnsToDrop = array_values(array_filter(
                ['store_name', 'store_slug', 'tagline', 'description', 'logo_media_id', 'favicon_media_id'],
                fn (string $column): bool => Schema::hasColumn('store_settings', $column),
            ));

            foreach (['logo_media_id', 'favicon_media_id'] as $mediaColumn) {
                if (Schema::hasColumn('store_settings', $mediaColumn)) {
                    $table->dropForeign([$mediaColumn]);
                }
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            if (! Schema::hasColumn('store_settings', 'catalog_visible')) {
                $table->boolean('catalog_visible')->default(true)->after('maintenance_message');
            }

            if (! Schema::hasColumn('store_settings', 'checkout_enabled')) {
                $table->boolean('checkout_enabled')->default(true)->after('catalog_visible');
            }

            if (! Schema::hasColumn('store_settings', 'guest_checkout_allowed')) {
                $table->boolean('guest_checkout_allowed')->default(true)->after('checkout_enabled');
            }

            if (! Schema::hasColumn('store_settings', 'shipping_enabled')) {
                $table->boolean('shipping_enabled')->default(true)->after('guest_checkout_allowed');
            }

            if (! Schema::hasColumn('store_settings', 'cod_enabled')) {
                $table->boolean('cod_enabled')->default(true)->after('shipping_enabled');
            }

            if (! Schema::hasColumn('store_settings', 'card_payment_enabled')) {
                $table->boolean('card_payment_enabled')->default(false)->after('cod_enabled');
            }
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
