<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SystemResetService
{
    /**
     * Tables that should never be reset by the owner action.
     *
     * @var array<int, string>
     */
    private array $protectedTables = [
        'users',
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'password_reset_tokens',
        'failed_jobs',
        'jobs',
        'job_batches',
    ];

    public function resetApplicationData(): void
    {
        set_time_limit(300);

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $tables = $this->getResettableTables($driver);

        if (empty($tables)) {
            return;
        }

        $this->disableForeignKeys($driver);

        try {
            foreach ($tables as $table) {
                $this->clearTable($driver, $table);
            }
        } finally {
            $this->enableForeignKeys($driver);
        }
    }

    /**
     * @return array<int, string>
     */
    private function getResettableTables(string $driver): array
    {
        $tables = $this->listTables($driver);

        return array_values(array_filter($tables, fn (string $table) => !in_array($table, $this->protectedTables, true)));
    }

    /**
     * @return array<int, string>
     */
    private function listTables(string $driver): array
    {
        return match ($driver) {
            'sqlite' => collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
                ->map(fn (object $row) => (string) $row->name)
                ->all(),
            default => collect(DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
                ->map(function (object $row): string {
                    $values = array_values((array) $row);

                    return (string) $values[0];
                })
                ->all(),
        };
    }

    private function clearTable(string $driver, string $table): void
    {
        if ($driver === 'sqlite') {
            DB::table($table)->delete();

            return;
        }

        DB::statement(sprintf('TRUNCATE TABLE `%s`', str_replace('`', '``', $table)));
    }

    private function disableForeignKeys(string $driver): void
    {
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    }

    private function enableForeignKeys(string $driver): void
    {
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
