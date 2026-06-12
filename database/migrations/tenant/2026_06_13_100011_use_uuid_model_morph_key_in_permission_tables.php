<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('model_has_roles')) {
            return;
        }

        $this->convertModelMorphKey(
            table: 'model_has_roles',
            pivotColumn: 'role_id',
        );
        $this->convertModelMorphKey(
            table: 'model_has_permissions',
            pivotColumn: 'permission_id',
        );
    }

    public function down(): void
    {
        // Irreversible without risking UUID data loss.
    }

    private function convertModelMorphKey(string $table, string $pivotColumn): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'model_id')) {
            return;
        }

        $column = DB::selectOne(
            'SELECT DATA_TYPE AS data_type FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, 'model_id'],
        );

        if ($column !== null && strtolower((string) $column->data_type) === 'char') {
            return;
        }

        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table],
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey->name}`");
        }

        DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
        DB::statement("ALTER TABLE `{$table}` MODIFY `model_id` CHAR(36) NOT NULL");
        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$pivotColumn}`, `model_id`, `model_type`)");

        if ($table === 'model_has_roles') {
            DB::statement(
                'ALTER TABLE `model_has_roles`
                 ADD CONSTRAINT `model_has_roles_role_id_foreign`
                 FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE',
            );
        }

        if ($table === 'model_has_permissions') {
            DB::statement(
                'ALTER TABLE `model_has_permissions`
                 ADD CONSTRAINT `model_has_permissions_permission_id_foreign`
                 FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE',
            );
        }
    }
};
