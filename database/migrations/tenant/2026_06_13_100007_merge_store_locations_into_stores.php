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
        if (! Schema::hasTable('store_locations')) {
            return;
        }

        $locations = DB::table('store_locations')->get();

        foreach ($locations as $location) {
            $exists = DB::table('stores')->where('id', $location->id)->exists();

            if ($exists) {
                DB::table('stores')->where('id', $location->id)->update([
                    'type' => $this->mapType($location->type),
                    'phone' => $location->phone,
                    'email' => $location->email,
                    'address' => $location->address,
                    'timezone' => $location->timezone,
                    'currency' => $location->currency,
                    'tax_rate' => $location->tax_rate,
                    'opening_hours' => $location->opening_hours,
                    'manager_id' => $location->manager_id,
                    'is_active' => $location->is_active,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('stores')->insert([
                    'id' => $location->id,
                    'name' => $location->name,
                    'slug' => Str::slug($location->name).'-'.Str::lower(Str::substr($location->id, 0, 8)),
                    'code' => $location->code,
                    'type' => $this->mapType($location->type),
                    'phone' => $location->phone,
                    'email' => $location->email,
                    'address' => $location->address,
                    'timezone' => $location->timezone,
                    'currency' => $location->currency,
                    'tax_rate' => $location->tax_rate,
                    'opening_hours' => $location->opening_hours,
                    'manager_id' => $location->manager_id,
                    'is_primary' => false,
                    'is_active' => $location->is_active,
                    'sort_order' => 0,
                    'created_at' => $location->created_at,
                    'updated_at' => $location->updated_at,
                    'deleted_at' => $location->deleted_at,
                ]);
            }

            if ($location->address && is_string($location->address)) {
                $address = json_decode($location->address, true);
            } else {
                $address = is_array($location->address) ? $location->address : [];
            }

            if ($address !== []) {
                DB::table('store_addresses')->insert([
                    'store_id' => $location->id,
                    'type' => 'primary',
                    'name' => $location->name,
                    'address_line_1' => $address['address_line_1'] ?? $address['line1'] ?? 'Unknown',
                    'address_line_2' => $address['address_line_2'] ?? $address['line2'] ?? null,
                    'city' => $address['city'] ?? 'Unknown',
                    'state' => $address['state'] ?? 'Unknown',
                    'postal_code' => $address['postal_code'] ?? $address['zip'] ?? '00000',
                    'country' => $address['country'] ?? 'US',
                    'phone' => $location->phone,
                    'email' => $location->email,
                    'is_default' => true,
                    'operating_hours' => $location->opening_hours,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (['location_inventory', 'location_staff', 'location_sales'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'location_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id');
            });

            DB::table($tableName)->update(['store_id' => DB::raw('location_id')]);

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('store_locations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible merge migration.
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            'office' => 'hybrid',
            'warehouse' => 'hybrid',
            default => $type,
        };
    }
};
