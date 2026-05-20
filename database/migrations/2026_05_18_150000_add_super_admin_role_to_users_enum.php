<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safe, non-destructive enum extension for MySQL/MariaDB only.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::connection()->getDatabaseName();
        if (! $database) {
            return;
        }

        $column = DB::selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'
             LIMIT 1",
            [$database]
        );

        $columnType = is_object($column) ? ($column->COLUMN_TYPE ?? null) : null;
        if (! is_string($columnType) || $columnType === '') {
            return;
        }

        if (str_contains($columnType, 'Super Admin')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `users`
             MODIFY COLUMN `role` ENUM('Super Admin','Admin','Karyawan') NOT NULL DEFAULT 'Karyawan'"
        );
    }

    public function down(): void
    {
        // Intentionally left as no-op to avoid destructive enum rollback in production.
    }
};

