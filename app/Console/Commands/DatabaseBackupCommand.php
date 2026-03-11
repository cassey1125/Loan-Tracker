<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup-daily {--retention=14}';

    protected $description = 'Create a daily database backup file.';

    public function handle(): int
    {
        $config = $this->getConnectionConfig();

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);
        $this->deleteInvalidBackups($backupDir);

        $timestamp = now()->format('Ymd_His');
        $driver = $config['driver'] ?? null;

        if ($driver === 'sqlite') {
            $source = $config['database'];
            $target = "{$backupDir}/backup_{$timestamp}.sqlite";
            File::copy($source, $target);
            $this->info("SQLite backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        if ($driver === 'pgsql') {
            $target = "{$backupDir}/backup_{$timestamp}.sql";
            $command = sprintf(
                'pg_dump -h %s -p %s -U %s -d %s -f "%s"',
                escapeshellarg($config['host']),
                escapeshellarg((string) $config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                $target
            );

            $result = Process::env(['PGPASSWORD' => $config['password'] ?? ''])->run($command);

            if (!$result->successful()) {
                $this->error('PostgreSQL backup failed: ' . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info("PostgreSQL backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $target = "{$backupDir}/backup_{$timestamp}.sql";
            $mysqldump = $this->resolveBinaryPath('DB_MYSQLDUMP_PATH', [
                'mysqldump',
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\mysqldump.exe',
            ]);

            if (!$mysqldump) {
                $fallbackError = $this->createMySqlFallbackBackup($target);
                if ($fallbackError === null) {
                    $this->info("MySQL backup created: {$target}");
                    $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
                    return self::SUCCESS;
                }

                $this->error('MySQL backup failed: mysqldump executable not found. Fallback export failed: ' . $fallbackError);
                return self::FAILURE;
            }

            $result = Process::env(['MYSQL_PWD' => $config['password'] ?? ''])->run([
                $mysqldump,
                '--result-file='.$target,
                '-h', (string) $config['host'],
                '-P', (string) $config['port'],
                '-u', (string) $config['username'],
                (string) $config['database'],
            ]);
            if (!$result->successful()) {
                if (File::exists($target) && File::size($target) === 0) {
                    File::delete($target);
                }

                $fallbackError = $this->createMySqlFallbackBackup($target);
                if ($fallbackError === null) {
                    $this->info("MySQL backup created: {$target}");
                    $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
                    return self::SUCCESS;
                }

                $error = trim($result->errorOutput() ?: $result->output());
                $this->error('MySQL backup failed: ' . $error . ' Fallback export failed: ' . $fallbackError);
                return self::FAILURE;
            }

            if (!File::exists($target) || File::size($target) <= 0) {
                if (File::exists($target)) {
                    File::delete($target);
                }

                $this->error('MySQL backup failed: no backup file was created.');
                return self::FAILURE;
            }

            $this->info("MySQL backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        $this->error('Unsupported database driver for automated backup: ' . $driver);
        return self::FAILURE;
    }

    protected function getConnectionConfig(): array
    {
        $defaultConnection = config('database.default');

        return (array) config("database.connections.{$defaultConnection}", []);
    }

    protected function pruneOldBackups(string $backupDir, int $retentionDays): void
    {
        $files = File::files($backupDir);
        $threshold = now()->subDays($retentionDays)->getTimestamp();

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                File::delete($file->getPathname());
            }
        }
    }

    protected function deleteInvalidBackups(string $backupDir): void
    {
        foreach (File::files($backupDir) as $file) {
            if ($file->getSize() <= 0) {
                File::delete($file->getPathname());
            }
        }
    }

    protected function resolveBinaryPath(string $envKey, array $candidates): ?string
    {
        $configured = env($envKey);
        if (is_string($configured) && $configured !== '' && File::exists($configured)) {
            return $configured;
        }

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, ':')) {
                if (File::exists($candidate)) {
                    return $candidate;
                }

                continue;
            }

            $resolved = trim((string) shell_exec('where ' . escapeshellarg($candidate) . ' 2>NUL'));
            if ($resolved !== '') {
                $first = preg_split('/\r\n|\r|\n/', $resolved)[0] ?? null;
                if ($first) {
                    return $first;
                }
            }
        }

        return null;
    }

    protected function createMySqlFallbackBackup(string $target): ?string
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            $lines = [
                '-- Loan Tracker fallback SQL backup',
                '-- Generated at ' . now()->toDateTimeString(),
                'SET FOREIGN_KEY_CHECKS=0;',
                '',
            ];

            $tables = collect(DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
                ->map(function (object $row): string {
                    $values = array_values((array) $row);

                    return (string) $values[0];
                })
                ->all();

            foreach ($tables as $table) {
                $tableName = str_replace('`', '``', $table);
                $createRow = (array) DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createSql = end($createRow);

                $lines[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                $lines[] = rtrim((string) $createSql, ';') . ';';

                $rows = DB::table($table)->get();
                foreach ($rows as $row) {
                    $values = array_map(fn ($value) => $this->sqlLiteral($pdo, $value), (array) $row);
                    $columns = array_map(fn ($column) => '`' . str_replace('`', '``', $column) . '`', array_keys((array) $row));
                    $lines[] = sprintf(
                        'INSERT INTO `%s` (%s) VALUES (%s);',
                        $tableName,
                        implode(', ', $columns),
                        implode(', ', $values),
                    );
                }

                $lines[] = '';
            }

            $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

            File::put($target, implode(PHP_EOL, $lines) . PHP_EOL);

            if (!File::exists($target) || File::size($target) <= 0) {
                return 'Fallback export produced no file.';
            }

            return null;
        } catch (\Throwable $e) {
            if (File::exists($target) && File::size($target) === 0) {
                File::delete($target);
            }

            return $e->getMessage();
        }
    }

    protected function sqlLiteral(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }
}
